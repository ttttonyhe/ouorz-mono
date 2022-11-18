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
use function extension_loaded;
use function in_array;
use InvalidArgumentException;
use function is_array;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\BigInteger;
use RuntimeException;

/**
 * @internal
 */
class RSAKey
{
    /**
     * @var array
     */
    private $values = [];

    /**
     * RSAKey constructor.
     */
    private function __construct(array $data)
    {
        $this->loadJWK($data);
    }

    /**
     * @return RSAKey
     */
    public static function createFromKeyDetails(array $details): self
    {
        $values = ['kty' => 'RSA'];
        $keys = [
            'n' => 'n',
            'e' => 'e',
            'd' => 'd',
            'p' => 'p',
            'q' => 'q',
            'dp' => 'dmp1',
            'dq' => 'dmq1',
            'qi' => 'iqmp',
        ];
        foreach ($details as $key => $value) {
            if (in_array($key, $keys, true)) {
                $value = Base64Url::encode($value);
                $values[array_search($key, $keys, true)] = $value;
            }
        }

        return new self($values);
    }

    /**
     * @throws RuntimeException         if the extension OpenSSL is not available
     * @throws InvalidArgumentException if the key cannot be loaded
     *
     * @return RSAKey
     */
    public static function createFromPEM(string $pem): self
    {
        if (!extension_loaded('openssl')) {
            throw new RuntimeException('Please install the OpenSSL extension');
        }
        $res = openssl_pkey_get_private($pem);
        if (false === $res) {
            $res = openssl_pkey_get_public($pem);
        }
        if (false === $res) {
            throw new InvalidArgumentException('Unable to load the key.');
        }

        $details = openssl_pkey_get_details($res);
        if (!is_array($details) || !isset($details['rsa'])) {
            throw new InvalidArgumentException('Unable to load the key.');
        }

        return self::createFromKeyDetails($details['rsa']);
    }

    /**
     * @return RSAKey
     */
    public static function createFromJWK(JWK $jwk): self
    {
        return new self($jwk->all());
    }

    public function isPublic(): bool
    {
        return !array_key_exists('d', $this->values);
    }

    /**
     * @param RSAKey $private
     *
     * @return RSAKey
     */
    public static function toPublic(self $private): self
    {
        $data = $private->toArray();
        $keys = ['p', 'd', 'q', 'dp', 'dq', 'qi'];
        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                unset($data[$key]);
            }
        }

        return new self($data);
    }

    public function toArray(): array
    {
        return $this->values;
    }

    public function toJwk(): JWK
    {
        return new JWK($this->values);
    }

    /**
     * This method will try to add Chinese Remainder Theorem (CRT) parameters.
     * With those primes, the decryption process is really fast.
     */
    public function optimize(): void
    {
        if (array_key_exists('d', $this->values)) {
            $this->populateCRT();
        }
    }

    /**
     * @throws InvalidArgumentException if the key is invalid or not an RSA key
     */
    private function loadJWK(array $jwk): void
    {
        if (!array_key_exists('kty', $jwk)) {
            throw new InvalidArgumentException('The key parameter "kty" is missing.');
        }
        if ('RSA' !== $jwk['kty']) {
            throw new InvalidArgumentException('The JWK is not a RSA key.');
        }

        $this->values = $jwk;
    }

    /**
     * This method adds Chinese Remainder Theorem (CRT) parameters if primes 'p' and 'q' are available.
     * If 'p' and 'q' are missing, they are computed and added to the key data.
     */
    private function populateCRT(): void
    {
        if (!array_key_exists('p', $this->values) && !array_key_exists('q', $this->values)) {
            $d = BigInteger::createFromBinaryString(Base64Url::decode($this->values['d']));
            $e = BigInteger::createFromBinaryString(Base64Url::decode($this->values['e']));
            $n = BigInteger::createFromBinaryString(Base64Url::decode($this->values['n']));

            [$p, $q] = $this->findPrimeFactors($d, $e, $n);
            $this->values['p'] = Base64Url::encode($p->toBytes());
            $this->values['q'] = Base64Url::encode($q->toBytes());
        }

        if (array_key_exists('dp', $this->values) && array_key_exists('dq', $this->values) && array_key_exists('qi', $this->values)) {
            return;
        }

        $one = BigInteger::createFromDecimal(1);
        $d = BigInteger::createFromBinaryString(Base64Url::decode($this->values['d']));
        $p = BigInteger::createFromBinaryString(Base64Url::decode($this->values['p']));
        $q = BigInteger::createFromBinaryString(Base64Url::decode($this->values['q']));

        $this->values['dp'] = Base64Url::encode($d->mod($p->subtract($one))->toBytes());
        $this->values['dq'] = Base64Url::encode($d->mod($q->subtract($one))->toBytes());
        $this->values['qi'] = Base64Url::encode($q->modInverse($p)->toBytes());
    }

    /**
     * @throws RuntimeException if the prime factors cannot be found
     *
     * @return BigInteger[]
     */
    private function findPrimeFactors(BigInteger $d, BigInteger $e, BigInteger $n): array
    {
        $zero = BigInteger::createFromDecimal(0);
        $one = BigInteger::createFromDecimal(1);
        $two = BigInteger::createFromDecimal(2);

        $k = $d->multiply($e)->subtract($one);

        if ($k->isEven()) {
            $r = $k;
            $t = $zero;

            do {
                $r = $r->divide($two);
                $t = $t->add($one);
            } while ($r->isEven());

            $found = false;
            $y = null;

            for ($i = 1; $i <= 100; ++$i) {
                $g = BigInteger::random($n->subtract($one));
                $y = $g->modPow($r, $n);

                if ($y->equals($one) || $y->equals($n->subtract($one))) {
                    continue;
                }

                for ($j = $one; $j->lowerThan($t->subtract($one)); $j = $j->add($one)) {
                    $x = $y->modPow($two, $n);

                    if ($x->equals($one)) {
                        $found = true;

                        break;
                    }

                    if ($x->equals($n->subtract($one))) {
                        continue;
                    }

                    $y = $x;
                }

                $x = $y->modPow($two, $n);
                if ($x->equals($one)) {
                    $found = true;

                    break;
                }
            }
            if (null === $y) {
                throw new InvalidArgumentException('Unable to find prime factors.');
            }
            if (true === $found) {
                $p = $y->subtract($one)->gcd($n);
                $q = $n->divide($p);

                return [$p, $q];
            }
        }

        throw new InvalidArgumentException('Unable to find prime factors.');
    }
}
