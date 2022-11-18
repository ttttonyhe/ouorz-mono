<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2020 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Jose\Component\KeyManagement\KeyConverter;

use function array_key_exists;
use Base64Url\Base64Url;
use function count;
use FG\ASN1\ASNObject;
use FG\ASN1\Exception\ParserException;
use FG\ASN1\ExplicitlyTaggedObject;
use FG\ASN1\Universal\BitString;
use FG\ASN1\Universal\Integer;
use FG\ASN1\Universal\ObjectIdentifier;
use FG\ASN1\Universal\OctetString;
use FG\ASN1\Universal\Sequence;
use InvalidArgumentException;
use function is_array;
use function is_string;

/**
 * @internal
 */
class ECKey
{
    /**
     * @var array
     */
    private $values = [];

    private function __construct(array $data)
    {
        $this->loadJWK($data);
    }

    public static function createFromPEM(string $pem): self
    {
        $data = self::loadPEM($pem);

        return new self($data);
    }

    /**
     * @param ECKey $private
     *
     * @return ECKey
     */
    public static function toPublic(self $private): self
    {
        $data = $private->toArray();
        if (array_key_exists('d', $data)) {
            unset($data['d']);
        }

        return new self($data);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->values;
    }

    /**
     * @throws InvalidArgumentException if the key cannot be loaded
     * @throws ParserException          if the key cannot be loaded
     */
    private static function loadPEM(string $data): array
    {
        $data = base64_decode(preg_replace('#-.*-|\r|\n#', '', $data), true);
        $asnObject = ASNObject::fromBinary($data);
        if (!$asnObject instanceof Sequence) {
            throw new InvalidArgumentException('Unable to load the key.');
        }
        $children = $asnObject->getChildren();
        if (self::isPKCS8($children)) {
            $children = self::loadPKCS8($children);
        }

        if (4 === count($children)) {
            return self::loadPrivatePEM($children);
        }
        if (2 === count($children)) {
            return self::loadPublicPEM($children);
        }

        throw new InvalidArgumentException('Unable to load the key.');
    }

    /**
     * @param ASNObject[] $children
     *
     * @throws InvalidArgumentException if the key cannot be loaded
     * @throws ParserException          if the key cannot be loaded
     */
    private static function loadPKCS8(array $children): array
    {
        $binary = hex2bin($children[2]->getContent());
        $asnObject = ASNObject::fromBinary($binary);
        if (!$asnObject instanceof Sequence) {
            throw new InvalidArgumentException('Unable to load the key.');
        }

        return $asnObject->getChildren();
    }

    /**
     * @throws InvalidArgumentException if the key cannot be loaded
     */
    private static function loadPublicPEM(array $children): array
    {
        if (!$children[0] instanceof Sequence) {
            throw new InvalidArgumentException('Unsupported key type.');
        }

        $sub = $children[0]->getChildren();
        if (!$sub[0] instanceof ObjectIdentifier) {
            throw new InvalidArgumentException('Unsupported key type.');
        }
        if ('1.2.840.10045.2.1' !== $sub[0]->getContent()) {
            throw new InvalidArgumentException('Unsupported key type.');
        }
        if (!$sub[1] instanceof ObjectIdentifier) {
            throw new InvalidArgumentException('Unsupported key type.');
        }
        if (!$children[1] instanceof BitString) {
            throw new InvalidArgumentException('Unable to load the key.');
        }

        $bits = $children[1]->getContent();
        $bits_length = mb_strlen($bits, '8bit');
        if (0 !== mb_strpos($bits, '04', 0, '8bit')) {
            throw new InvalidArgumentException('Unsupported key type');
        }

        $values = ['kty' => 'EC'];
        $values['crv'] = self::getCurve($sub[1]->getContent());

        $xBin = hex2bin(mb_substr($bits, 2, ($bits_length - 2) / 2, '8bit'));
        $yBin = hex2bin(mb_substr($bits, (int) (($bits_length - 2) / 2 + 2), ($bits_length - 2) / 2, '8bit'));
        if (!is_string($xBin) || !is_string($yBin)) {
            throw new InvalidArgumentException('Unable to load the key.');
        }

        $values['x'] = Base64Url::encode($xBin);
        $values['y'] = Base64Url::encode($yBin);

        return $values;
    }

    /**
     * @throws InvalidArgumentException if the OID is not supported
     */
    private static function getCurve(string $oid): string
    {
        $curves = self::getSupportedCurves();
        $curve = array_search($oid, $curves, true);
        if (!is_string($curve)) {
            throw new InvalidArgumentException('Unsupported OID.');
        }

        return $curve;
    }

    private static function getSupportedCurves(): array
    {
        return [
            'P-256' => '1.2.840.10045.3.1.7',
            'P-384' => '1.3.132.0.34',
            'P-521' => '1.3.132.0.35',
        ];
    }

    /**
     * @throws InvalidArgumentException if the key cannot be loaded
     */
    private static function verifyVersion(ASNObject $children): void
    {
        if (!$children instanceof Integer || '1' !== $children->getContent()) {
            throw new InvalidArgumentException('Unable to load the key.');
        }
    }

    /**
     * @throws InvalidArgumentException if the key cannot be loaded
     */
    private static function getXAndY(ASNObject $children, string &$x, string &$y): void
    {
        if (!$children instanceof ExplicitlyTaggedObject || !is_array($children->getContent())) {
            throw new InvalidArgumentException('Unable to load the key.');
        }
        if (!$children->getContent()[0] instanceof BitString) {
            throw new InvalidArgumentException('Unable to load the key.');
        }

        $bits = $children->getContent()[0]->getContent();
        $bits_length = mb_strlen($bits, '8bit');

        if (0 !== mb_strpos($bits, '04', 0, '8bit')) {
            throw new InvalidArgumentException('Unsupported key type');
        }

        $x = mb_substr($bits, 2, (int) (($bits_length - 2) / 2), '8bit');
        $y = mb_substr($bits, (int) (($bits_length - 2) / 2 + 2), (int) (($bits_length - 2) / 2), '8bit');
    }

    /**
     * @throws InvalidArgumentException if the key cannot be loaded
     */
    private static function getD(ASNObject $children): string
    {
        if (!$children instanceof OctetString) {
            throw new InvalidArgumentException('Unable to load the key.');
        }

        return $children->getContent();
    }

    /**
     * @throws InvalidArgumentException if the key cannot be loaded
     */
    private static function loadPrivatePEM(array $children): array
    {
        self::verifyVersion($children[0]);
        $x = '';
        $y = '';
        $d = self::getD($children[1]);
        self::getXAndY($children[3], $x, $y);

        if (!$children[2] instanceof ExplicitlyTaggedObject || !is_array($children[2]->getContent())) {
            throw new InvalidArgumentException('Unable to load the key.');
        }
        if (!$children[2]->getContent()[0] instanceof ObjectIdentifier) {
            throw new InvalidArgumentException('Unable to load the key.');
        }

        $curve = $children[2]->getContent()[0]->getContent();
        $dBin = hex2bin($d);
        $xBin = hex2bin($x);
        $yBin = hex2bin($y);
        if (!is_string($dBin) || !is_string($xBin) || !is_string($yBin)) {
            throw new InvalidArgumentException('Unable to load the key.');
        }

        $values = ['kty' => 'EC'];
        $values['crv'] = self::getCurve($curve);
        $values['d'] = Base64Url::encode($dBin);
        $values['x'] = Base64Url::encode($xBin);
        $values['y'] = Base64Url::encode($yBin);

        return $values;
    }

    /**
     * @param ASNObject[] $children
     */
    private static function isPKCS8(array $children): bool
    {
        if (3 !== count($children)) {
            return false;
        }

        $classes = [0 => Integer::class, 1 => Sequence::class, 2 => OctetString::class];
        foreach ($classes as $k => $class) {
            if (!$children[$k] instanceof $class) {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws InvalidArgumentException if the key is invalid
     */
    private function loadJWK(array $jwk): void
    {
        $keys = [
            'kty' => 'The key parameter "kty" is missing.',
            'crv' => 'Curve parameter is missing',
            'x' => 'Point parameters are missing.',
            'y' => 'Point parameters are missing.',
        ];
        foreach ($keys as $k => $v) {
            if (!array_key_exists($k, $jwk)) {
                throw new InvalidArgumentException($v);
            }
        }

        if ('EC' !== $jwk['kty']) {
            throw new InvalidArgumentException('JWK is not an Elliptic Curve key.');
        }
        $this->values = $jwk;
    }
}
