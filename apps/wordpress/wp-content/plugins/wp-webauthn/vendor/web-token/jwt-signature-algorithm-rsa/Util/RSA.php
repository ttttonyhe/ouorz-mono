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

namespace Jose\Component\Signature\Algorithm\Util;

use function chr;
use InvalidArgumentException;
use Jose\Component\Core\Util\BigInteger;
use Jose\Component\Core\Util\Hash;
use Jose\Component\Core\Util\RSAKey;
use function ord;
use RuntimeException;

/**
 * @internal
 */
class RSA
{
    /**
     * Probabilistic Signature Scheme.
     */
    public const SIGNATURE_PSS = 1;

    /**
     * Use the PKCS#1.
     */
    public const SIGNATURE_PKCS1 = 2;

    /**
     * @throws RuntimeException         if the data cannot be signed
     * @throws InvalidArgumentException if the signature mode is not supported
     */
    public static function sign(RSAKey $key, string $message, string $hash, int $mode): string
    {
        switch ($mode) {
            case self::SIGNATURE_PSS:
                return self::signWithPSS($key, $message, $hash);

            case self::SIGNATURE_PKCS1:
                $result = openssl_sign($message, $signature, $key->toPEM(), $hash);
                if (true !== $result) {
                    throw new RuntimeException('Unable to sign the data');
                }

                return $signature;

            default:
                throw new InvalidArgumentException('Unsupported mode.');
        }
    }

    /**
     * Create a signature.
     */
    public static function signWithPSS(RSAKey $key, string $message, string $hash): string
    {
        $em = self::encodeEMSAPSS($message, 8 * $key->getModulusLength() - 1, Hash::$hash());
        $message = BigInteger::createFromBinaryString($em);
        $signature = RSAKey::exponentiate($key, $message);

        return self::convertIntegerToOctetString($signature, $key->getModulusLength());
    }

    /**
     * Create a signature.
     *
     * @deprecated Please use openssl_sign
     */
    public static function signWithPKCS15(RSAKey $key, string $message, string $hash): string
    {
        $em = self::encodeEMSA15($message, $key->getModulusLength(), Hash::$hash());
        $message = BigInteger::createFromBinaryString($em);
        $signature = RSAKey::exponentiate($key, $message);

        return self::convertIntegerToOctetString($signature, $key->getModulusLength());
    }

    /**
     * @throws InvalidArgumentException if the signature mode is not supported
     */
    public static function verify(RSAKey $key, string $message, string $signature, string $hash, int $mode): bool
    {
        switch ($mode) {
            case self::SIGNATURE_PSS:
                return self::verifyWithPSS($key, $message, $signature, $hash);

            case self::SIGNATURE_PKCS1:
                return 1 === openssl_verify($message, $signature, $key->toPEM(), $hash);

            default:
                throw new InvalidArgumentException('Unsupported mode.');
        }
    }

    /**
     * Verifies a signature.
     *
     * @throws RuntimeException if the signature cannot be verified
     */
    public static function verifyWithPSS(RSAKey $key, string $message, string $signature, string $hash): bool
    {
        if (mb_strlen($signature, '8bit') !== $key->getModulusLength()) {
            throw new RuntimeException();
        }
        $s2 = BigInteger::createFromBinaryString($signature);
        $m2 = RSAKey::exponentiate($key, $s2);
        $em = self::convertIntegerToOctetString($m2, $key->getModulusLength());
        $modBits = 8 * $key->getModulusLength();

        return self::verifyEMSAPSS($message, $em, $modBits - 1, Hash::$hash());
    }

    /**
     * Verifies a signature.
     *
     * @deprecated Please use openssl_sign
     *
     * @throws RuntimeException if the signature cannot be verified
     */
    public static function verifyWithPKCS15(RSAKey $key, string $message, string $signature, string $hash): bool
    {
        if (mb_strlen($signature, '8bit') !== $key->getModulusLength()) {
            throw new RuntimeException();
        }
        $signature = BigInteger::createFromBinaryString($signature);
        $m2 = RSAKey::exponentiate($key, $signature);
        $em = self::convertIntegerToOctetString($m2, $key->getModulusLength());

        return hash_equals($em, self::encodeEMSA15($message, $key->getModulusLength(), Hash::$hash()));
    }

    /**
     * @throws RuntimeException if the value cannot be converted
     */
    private static function convertIntegerToOctetString(BigInteger $x, int $xLen): string
    {
        $x = $x->toBytes();
        if (mb_strlen($x, '8bit') > $xLen) {
            throw new RuntimeException();
        }

        return str_pad($x, $xLen, chr(0), STR_PAD_LEFT);
    }

    /**
     * MGF1.
     */
    private static function getMGF1(string $mgfSeed, int $maskLen, Hash $mgfHash): string
    {
        $t = '';
        $count = ceil($maskLen / $mgfHash->getLength());
        for ($i = 0; $i < $count; ++$i) {
            $c = pack('N', $i);
            $t .= $mgfHash->hash($mgfSeed.$c);
        }

        return mb_substr($t, 0, $maskLen, '8bit');
    }

    /**
     * EMSA-PSS-ENCODE.
     *
     * @throws RuntimeException if the message length is invalid
     */
    private static function encodeEMSAPSS(string $message, int $modulusLength, Hash $hash): string
    {
        $emLen = ($modulusLength + 1) >> 3;
        $sLen = $hash->getLength();
        $mHash = $hash->hash($message);
        if ($emLen <= $hash->getLength() + $sLen + 2) {
            throw new RuntimeException();
        }
        $salt = random_bytes($sLen);
        $m2 = "\0\0\0\0\0\0\0\0".$mHash.$salt;
        $h = $hash->hash($m2);
        $ps = str_repeat(chr(0), $emLen - $sLen - $hash->getLength() - 2);
        $db = $ps.chr(1).$salt;
        $dbMask = self::getMGF1($h, $emLen - $hash->getLength() - 1, $hash);
        $maskedDB = $db ^ $dbMask;
        $maskedDB[0] = ~chr(0xFF << ($modulusLength & 7)) & $maskedDB[0];
        $em = $maskedDB.$h.chr(0xBC);

        return $em;
    }

    /**
     * EMSA-PSS-VERIFY.
     *
     * @throws InvalidArgumentException if the signature cannot be verified
     */
    private static function verifyEMSAPSS(string $m, string $em, int $emBits, Hash $hash): bool
    {
        $emLen = ($emBits + 1) >> 3;
        $sLen = $hash->getLength();
        $mHash = $hash->hash($m);
        if ($emLen < $hash->getLength() + $sLen + 2) {
            throw new InvalidArgumentException();
        }
        if ($em[mb_strlen($em, '8bit') - 1] !== chr(0xBC)) {
            throw new InvalidArgumentException();
        }
        $maskedDB = mb_substr($em, 0, -$hash->getLength() - 1, '8bit');
        $h = mb_substr($em, -$hash->getLength() - 1, $hash->getLength(), '8bit');
        $temp = chr(0xFF << ($emBits & 7));
        if ((~$maskedDB[0] & $temp) !== $temp) {
            throw new InvalidArgumentException();
        }
        $dbMask = self::getMGF1($h, $emLen - $hash->getLength() - 1, $hash/*MGF*/);
        $db = $maskedDB ^ $dbMask;
        $db[0] = ~chr(0xFF << ($emBits & 7)) & $db[0];
        $temp = $emLen - $hash->getLength() - $sLen - 2;
        if (mb_substr($db, 0, $temp, '8bit') !== str_repeat(chr(0), $temp)) {
            throw new InvalidArgumentException();
        }
        if (1 !== ord($db[$temp])) {
            throw new InvalidArgumentException();
        }
        $salt = mb_substr($db, $temp + 1, null, '8bit'); // should be $sLen long
        $m2 = "\0\0\0\0\0\0\0\0".$mHash.$salt;
        $h2 = $hash->hash($m2);

        return hash_equals($h, $h2);
    }

    /**
     * @throws RuntimeException if the value cannot be encoded
     */
    private static function encodeEMSA15(string $m, int $emBits, Hash $hash): string
    {
        $h = $hash->hash($m);
        $t = $hash->t();
        $t .= $h;
        $tLen = mb_strlen($t, '8bit');
        if ($emBits < $tLen + 11) {
            throw new RuntimeException();
        }
        $ps = str_repeat(chr(0xFF), $emBits - $tLen - 3);

        return "\0\1{$ps}\0{$t}";
    }
}
