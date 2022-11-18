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

use function in_array;
use InvalidArgumentException;
use function is_array;
use Jose\Component\Core\JWK;

/**
 * @internal
 */
class KeyChecker
{
    public static function checkKeyUsage(JWK $key, string $usage): void
    {
        if ($key->has('use')) {
            self::checkUsage($key, $usage);
        }
        if ($key->has('key_ops')) {
            self::checkOperation($key, $usage);
        }
    }

    /**
     * @throws InvalidArgumentException if the key is not suitable for the selected algorithm
     */
    public static function checkKeyAlgorithm(JWK $key, string $algorithm): void
    {
        if (!$key->has('alg')) {
            return;
        }
        if ($key->get('alg') !== $algorithm) {
            throw new InvalidArgumentException(sprintf('Key is only allowed for algorithm "%s".', $key->get('alg')));
        }
    }

    /**
     * @throws InvalidArgumentException if the key is not suitable for the selected operation
     */
    private static function checkOperation(JWK $key, string $usage): void
    {
        $ops = $key->get('key_ops');
        if (!is_array($ops)) {
            throw new InvalidArgumentException('Invalid key parameter "key_ops". Should be a list of key operations');
        }

        switch ($usage) {
            case 'verification':
                if (!in_array('verify', $ops, true)) {
                    throw new InvalidArgumentException('Key cannot be used to verify a signature');
                }

                break;

            case 'signature':
                if (!in_array('sign', $ops, true)) {
                    throw new InvalidArgumentException('Key cannot be used to sign');
                }

                break;

            case 'encryption':
                if (!in_array('encrypt', $ops, true) && !in_array('wrapKey', $ops, true) && !in_array('deriveKey', $ops, true)) {
                    throw new InvalidArgumentException('Key cannot be used to encrypt');
                }

                break;

            case 'decryption':
                if (!in_array('decrypt', $ops, true) && !in_array('unwrapKey', $ops, true) && !in_array('deriveBits', $ops, true)) {
                    throw new InvalidArgumentException('Key cannot be used to decrypt');
                }

                break;

            default:
                throw new InvalidArgumentException('Unsupported key usage.');
        }
    }

    /**
     * @throws InvalidArgumentException if the key is not suitable for the selected operation
     */
    private static function checkUsage(JWK $key, string $usage): void
    {
        $use = $key->get('use');

        switch ($usage) {
            case 'verification':
            case 'signature':
                if ('sig' !== $use) {
                    throw new InvalidArgumentException('Key cannot be used to sign or verify a signature.');
                }

                break;

            case 'encryption':
            case 'decryption':
                if ('enc' !== $use) {
                    throw new InvalidArgumentException('Key cannot be used to encrypt or decrypt.');
                }

                break;

            default:
                throw new InvalidArgumentException('Unsupported key usage.');
        }
    }
}
