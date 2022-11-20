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

namespace Jose\Component\Core\Util;

use Base64Url\Base64Url;
use function extension_loaded;
use InvalidArgumentException;
use function is_array;
use Jose\Component\Core\JWK;
use RuntimeException;

/**
 * @internal
 */
class ECKey
{
    public static function convertToPEM(JWK $jwk): string
    {
        if ($jwk->has('d')) {
            return self::convertPrivateKeyToPEM($jwk);
        }

        return self::convertPublicKeyToPEM($jwk);
    }

    /**
     * @throws InvalidArgumentException if the curve is not supported
     */
    public static function convertPublicKeyToPEM(JWK $jwk): string
    {
        switch ($jwk->get('crv')) {
            case 'P-256':
                $der = self::p256PublicKey();

                break;

            case 'secp256k1':
                $der = self::p256KPublicKey();

                break;

            case 'P-384':
                $der = self::p384PublicKey();

                break;

            case 'P-521':
                $der = self::p521PublicKey();

                break;

            default:
                throw new InvalidArgumentException('Unsupported curve.');
        }
        $der .= self::getKey($jwk);
        $pem = '-----BEGIN PUBLIC KEY-----'.PHP_EOL;
        $pem .= chunk_split(base64_encode($der), 64, PHP_EOL);
        $pem .= '-----END PUBLIC KEY-----'.PHP_EOL;

        return $pem;
    }

    /**
     * @throws InvalidArgumentException if the curve is not supported
     */
    public static function convertPrivateKeyToPEM(JWK $jwk): string
    {
        switch ($jwk->get('crv')) {
            case 'P-256':
                $der = self::p256PrivateKey($jwk);

                break;

            case 'secp256k1':
                $der = self::p256KPrivateKey($jwk);

                break;

            case 'P-384':
                $der = self::p384PrivateKey($jwk);

                break;

            case 'P-521':
                $der = self::p521PrivateKey($jwk);

                break;

            default:
                throw new InvalidArgumentException('Unsupported curve.');
        }
        $der .= self::getKey($jwk);
        $pem = '-----BEGIN EC PRIVATE KEY-----'.PHP_EOL;
        $pem .= chunk_split(base64_encode($der), 64, PHP_EOL);
        $pem .= '-----END EC PRIVATE KEY-----'.PHP_EOL;

        return $pem;
    }

    /**
     * Creates a EC key with the given curve and additional values.
     *
     * @param string $curve  The curve
     * @param array  $values values to configure the key
     */
    public static function createECKey(string $curve, array $values = []): JWK
    {
        $jwk = self::createECKeyUsingOpenSSL($curve);
        $values = array_merge($values, $jwk);

        return new JWK($values);
    }

    /**
     * @throws InvalidArgumentException if the curve is not supported
     */
    private static function getNistCurveSize(string $curve): int
    {
        switch ($curve) {
            case 'P-256':
            case 'secp256k1':
                return 256;

            case 'P-384':
                return 384;

            case 'P-521':
                return 521;

            default:
                throw new InvalidArgumentException(sprintf('The curve "%s" is not supported.', $curve));
        }
    }

    /**
     * @throws RuntimeException if the extension OpenSSL is not available
     * @throws RuntimeException if the key cannot be created
     */
    private static function createECKeyUsingOpenSSL(string $curve): array
    {
        if (!extension_loaded('openssl')) {
            throw new RuntimeException('Please install the OpenSSL extension');
        }
        $key = openssl_pkey_new([
            'curve_name' => self::getOpensslCurveName($curve),
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ]);
        if (false === $key) {
            throw new RuntimeException('Unable to create the key');
        }
        $result = openssl_pkey_export($key, $out);
        if (false === $result) {
            throw new RuntimeException('Unable to create the key');
        }
        $res = openssl_pkey_get_private($out);
        if (false === $res) {
            throw new RuntimeException('Unable to create the key');
        }
        $details = openssl_pkey_get_details($res);
        if (false === $details) {
            throw new InvalidArgumentException('Unable to get the key details');
        }
        $nistCurveSize = self::getNistCurveSize($curve);

        return [
            'kty' => 'EC',
            'crv' => $curve,
            'd' => Base64Url::encode(str_pad($details['ec']['d'], (int) ceil($nistCurveSize / 8), "\0", STR_PAD_LEFT)),
            'x' => Base64Url::encode(str_pad($details['ec']['x'], (int) ceil($nistCurveSize / 8), "\0", STR_PAD_LEFT)),
            'y' => Base64Url::encode(str_pad($details['ec']['y'], (int) ceil($nistCurveSize / 8), "\0", STR_PAD_LEFT)),
        ];
    }

    /**
     * @throws InvalidArgumentException if the curve is not supported
     */
    private static function getOpensslCurveName(string $curve): string
    {
        switch ($curve) {
            case 'P-256':
                return 'prime256v1';

            case 'secp256k1':
                return 'secp256k1';

            case 'P-384':
                return 'secp384r1';

            case 'P-521':
                return 'secp521r1';

            default:
                throw new InvalidArgumentException(sprintf('The curve "%s" is not supported.', $curve));
        }
    }

    private static function p256PublicKey(): string
    {
        return pack(
            'H*',
            '3059' // SEQUENCE, length 89
                .'3013' // SEQUENCE, length 19
                    .'0607' // OID, length 7
                        .'2a8648ce3d0201' // 1.2.840.10045.2.1 = EC Public Key
                    .'0608' // OID, length 8
                        .'2a8648ce3d030107' // 1.2.840.10045.3.1.7 = P-256 Curve
                .'0342' // BIT STRING, length 66
                    .'00' // prepend with NUL - pubkey will follow
        );
    }

    private static function p256KPublicKey(): string
    {
        return pack(
            'H*',
            '3056' // SEQUENCE, length 86
                .'3010' // SEQUENCE, length 16
                    .'0607' // OID, length 7
                        .'2a8648ce3d0201' // 1.2.840.10045.2.1 = EC Public Key
                    .'0605' // OID, length 8
                        .'2B8104000A' // 1.3.132.0.10 secp256k1
                .'0342' // BIT STRING, length 66
                    .'00' // prepend with NUL - pubkey will follow
        );
    }

    private static function p384PublicKey(): string
    {
        return pack(
            'H*',
            '3076' // SEQUENCE, length 118
                .'3010' // SEQUENCE, length 16
                    .'0607' // OID, length 7
                        .'2a8648ce3d0201' // 1.2.840.10045.2.1 = EC Public Key
                    .'0605' // OID, length 5
                        .'2b81040022' // 1.3.132.0.34 = P-384 Curve
                .'0362' // BIT STRING, length 98
                    .'00' // prepend with NUL - pubkey will follow
        );
    }

    private static function p521PublicKey(): string
    {
        return pack(
            'H*',
            '30819b' // SEQUENCE, length 154
                .'3010' // SEQUENCE, length 16
                    .'0607' // OID, length 7
                        .'2a8648ce3d0201' // 1.2.840.10045.2.1 = EC Public Key
                    .'0605' // OID, length 5
                        .'2b81040023' // 1.3.132.0.35 = P-521 Curve
                .'038186' // BIT STRING, length 134
                    .'00' // prepend with NUL - pubkey will follow
        );
    }

    private static function p256PrivateKey(JWK $jwk): string
    {
        $d = unpack('H*', str_pad(Base64Url::decode($jwk->get('d')), 32, "\0", STR_PAD_LEFT));
        if (!is_array($d) || !isset($d[1])) {
            throw new InvalidArgumentException('Unable to get the private key');
        }

        return pack(
            'H*',
            '3077' // SEQUENCE, length 87+length($d)=32
                .'020101' // INTEGER, 1
                .'0420'   // OCTET STRING, length($d) = 32
                    .$d[1]
                .'a00a' // TAGGED OBJECT #0, length 10
                    .'0608' // OID, length 8
                        .'2a8648ce3d030107' // 1.3.132.0.34 = P-256 Curve
                .'a144' //  TAGGED OBJECT #1, length 68
                    .'0342' // BIT STRING, length 66
                    .'00' // prepend with NUL - pubkey will follow
        );
    }

    private static function p256KPrivateKey(JWK $jwk): string
    {
        $d = unpack('H*', str_pad(Base64Url::decode($jwk->get('d')), 32, "\0", STR_PAD_LEFT));
        if (!is_array($d) || !isset($d[1])) {
            throw new InvalidArgumentException('Unable to get the private key');
        }

        return pack(
            'H*',
            '3074' // SEQUENCE, length 84+length($d)=32
                .'020101' // INTEGER, 1
                .'0420'   // OCTET STRING, length($d) = 32
                    .$d[1]
                .'a007' // TAGGED OBJECT #0, length 7
                    .'0605' // OID, length 5
                        .'2b8104000a' //  1.3.132.0.10 secp256k1
                .'a144' //  TAGGED OBJECT #1, length 68
                    .'0342' // BIT STRING, length 66
                    .'00' // prepend with NUL - pubkey will follow
        );
    }

    private static function p384PrivateKey(JWK $jwk): string
    {
        $d = unpack('H*', str_pad(Base64Url::decode($jwk->get('d')), 48, "\0", STR_PAD_LEFT));
        if (!is_array($d) || !isset($d[1])) {
            throw new InvalidArgumentException('Unable to get the private key');
        }

        return pack(
            'H*',
            '3081a4' // SEQUENCE, length 116 + length($d)=48
                .'020101' // INTEGER, 1
                .'0430'   // OCTET STRING, length($d) = 30
                    .$d[1]
                .'a007' // TAGGED OBJECT #0, length 7
                    .'0605' // OID, length 5
                        .'2b81040022' // 1.3.132.0.34 = P-384 Curve
                .'a164' //  TAGGED OBJECT #1, length 100
                    .'0362' // BIT STRING, length 98
                    .'00' // prepend with NUL - pubkey will follow
        );
    }

    private static function p521PrivateKey(JWK $jwk): string
    {
        $d = unpack('H*', str_pad(Base64Url::decode($jwk->get('d')), 66, "\0", STR_PAD_LEFT));
        if (!is_array($d) || !isset($d[1])) {
            throw new InvalidArgumentException('Unable to get the private key');
        }

        return pack(
            'H*',
            '3081dc' // SEQUENCE, length 154 + length($d)=66
                .'020101' // INTEGER, 1
                .'0442'   // OCTET STRING, length(d) = 66
                    .$d[1]
                .'a007' // TAGGED OBJECT #0, length 7
                    .'0605' // OID, length 5
                        .'2b81040023' // 1.3.132.0.35 = P-521 Curve
                .'a18189' //  TAGGED OBJECT #1, length 137
                    .'038186' // BIT STRING, length 134
                    .'00' // prepend with NUL - pubkey will follow
        );
    }

    private static function getKey(JWK $jwk): string
    {
        $nistCurveSize = self::getNistCurveSize($jwk->get('crv'));
        $length = (int) ceil($nistCurveSize / 8);

        return
            "\04"
            .str_pad(Base64Url::decode($jwk->get('x')), $length, "\0", STR_PAD_LEFT)
            .str_pad(Base64Url::decode($jwk->get('y')), $length, "\0", STR_PAD_LEFT);
    }
}
