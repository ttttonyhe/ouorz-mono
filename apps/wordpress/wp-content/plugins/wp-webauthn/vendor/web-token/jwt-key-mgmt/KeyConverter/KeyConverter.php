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
use function extension_loaded;
use InvalidArgumentException;
use function is_array;
use function is_string;
use RuntimeException;
use Throwable;

/**
 * @internal
 */
class KeyConverter
{
    /**
     * @throws InvalidArgumentException if the certificate file cannot be read
     */
    public static function loadKeyFromCertificateFile(string $file): array
    {
        if (!file_exists($file)) {
            throw new InvalidArgumentException(sprintf('File "%s" does not exist.', $file));
        }
        $content = file_get_contents($file);
        if (!is_string($content)) {
            throw new InvalidArgumentException(sprintf('File "%s" cannot be read.', $file));
        }

        return self::loadKeyFromCertificate($content);
    }

    /**
     * @throws InvalidArgumentException if the OpenSSL extension is not available
     * @throws InvalidArgumentException if the certificate is invalid or cannot be loaded
     */
    public static function loadKeyFromCertificate(string $certificate): array
    {
        if (!extension_loaded('openssl')) {
            throw new RuntimeException('Please install the OpenSSL extension');
        }

        try {
            $res = openssl_x509_read($certificate);
            if (false === $res) {
                throw new InvalidArgumentException('Unable to load the certificate.');
            }
        } catch (Throwable $e) {
            $certificate = self::convertDerToPem($certificate);
            $res = openssl_x509_read($certificate);
        }
        if (false === $res) {
            throw new InvalidArgumentException('Unable to load the certificate.');
        }

        return self::loadKeyFromX509Resource($res);
    }

    /**
     * @param resource $res
     *
     * @throws InvalidArgumentException if the OpenSSL extension is not available
     * @throws InvalidArgumentException if the certificate is invalid or cannot be loaded
     */
    public static function loadKeyFromX509Resource($res): array
    {
        if (!extension_loaded('openssl')) {
            throw new RuntimeException('Please install the OpenSSL extension');
        }
        $key = openssl_get_publickey($res);
        if (false === $key) {
            throw new InvalidArgumentException('Unable to load the certificate.');
        }
        $details = openssl_pkey_get_details($key);
        if (!is_array($details)) {
            throw new InvalidArgumentException('Unable to load the certificate');
        }
        if (isset($details['key'])) {
            $values = self::loadKeyFromPEM($details['key']);
            openssl_x509_export($res, $out);
            $x5c = preg_replace('#-.*-#', '', $out);
            $x5c = preg_replace('~\R~', PHP_EOL, $x5c);
            if (!is_string($x5c)) {
                throw new InvalidArgumentException('Unable to load the certificate');
            }
            $x5c = trim($x5c);

            $x5tsha1 = openssl_x509_fingerprint($res, 'sha1', true);
            $x5tsha256 = openssl_x509_fingerprint($res, 'sha256', true);
            if (!is_string($x5tsha1) || !is_string($x5tsha256)) {
                throw new InvalidArgumentException('Unable to compute the certificate fingerprint');
            }

            $values['x5c'] = [$x5c];
            $values['x5t'] = Base64Url::encode($x5tsha1);
            $values['x5t#256'] = Base64Url::encode($x5tsha256);

            return $values;
        }

        throw new InvalidArgumentException('Unable to load the certificate');
    }

    public static function loadFromKeyFile(string $file, ?string $password = null): array
    {
        $content = file_get_contents($file);
        if (!is_string($content)) {
            throw new InvalidArgumentException('Unable to load the key from the file.');
        }

        return self::loadFromKey($content, $password);
    }

    public static function loadFromKey(string $key, ?string $password = null): array
    {
        try {
            return self::loadKeyFromDER($key, $password);
        } catch (Throwable $e) {
            return self::loadKeyFromPEM($key, $password);
        }
    }

    /**
     * Be careful! The certificate chain is loaded, but it is NOT VERIFIED by any mean!
     * It is mandatory to verify the root CA or intermediate  CA are trusted.
     * If not done, it may lead to potential security issues.
     *
     * @throws InvalidArgumentException if the certificate chain is empty
     * @throws InvalidArgumentException if the OpenSSL extension is not available
     */
    public static function loadFromX5C(array $x5c): array
    {
        if (0 === count($x5c)) {
            throw new InvalidArgumentException('The certificate chain is empty');
        }
        foreach ($x5c as $id => $cert) {
            $x5c[$id] = '-----BEGIN CERTIFICATE-----'.PHP_EOL.chunk_split($cert, 64, PHP_EOL).'-----END CERTIFICATE-----';
            $x509 = openssl_x509_read($x5c[$id]);
            if (false === $x509) {
                throw new InvalidArgumentException('Unable to load the certificate chain');
            }
            $parsed = openssl_x509_parse($x509);
            if (false === $parsed) {
                throw new InvalidArgumentException('Unable to load the certificate chain');
            }
        }

        return self::loadKeyFromCertificate(reset($x5c));
    }

    private static function loadKeyFromDER(string $der, ?string $password = null): array
    {
        $pem = self::convertDerToPem($der);

        return self::loadKeyFromPEM($pem, $password);
    }

    /**
     * @throws InvalidArgumentException if the OpenSSL extension is not available
     * @throws InvalidArgumentException if the key cannot be loaded
     */
    private static function loadKeyFromPEM(string $pem, ?string $password = null): array
    {
        if (1 === preg_match('#DEK-Info: (.+),(.+)#', $pem, $matches)) {
            $pem = self::decodePem($pem, $matches, $password);
        }

        if (!extension_loaded('openssl')) {
            throw new RuntimeException('Please install the OpenSSL extension');
        }
        self::sanitizePEM($pem);
        $res = openssl_pkey_get_private($pem);
        if (false === $res) {
            $res = openssl_pkey_get_public($pem);
        }
        if (false === $res) {
            throw new InvalidArgumentException('Unable to load the key.');
        }

        $details = openssl_pkey_get_details($res);
        if (!is_array($details) || !array_key_exists('type', $details)) {
            throw new InvalidArgumentException('Unable to get details of the key');
        }

        switch ($details['type']) {
            case OPENSSL_KEYTYPE_EC:
                $ec_key = ECKey::createFromPEM($pem);

                return $ec_key->toArray();

            case OPENSSL_KEYTYPE_RSA:
                $rsa_key = RSAKey::createFromPEM($pem);

                return $rsa_key->toArray();

            default:
                throw new InvalidArgumentException('Unsupported key type');
        }
    }

    /**
     * This method modifies the PEM to get 64 char lines and fix bug with old OpenSSL versions.
     */
    private static function sanitizePEM(string &$pem): void
    {
        preg_match_all('#(-.*-)#', $pem, $matches, PREG_PATTERN_ORDER);
        $ciphertext = preg_replace('#-.*-|\r|\n| #', '', $pem);

        $pem = $matches[0][0].PHP_EOL;
        $pem .= chunk_split($ciphertext, 64, PHP_EOL);
        $pem .= $matches[0][1].PHP_EOL;
    }

    /**
     * @param string[] $matches
     *
     * @throws InvalidArgumentException if the password to decrypt the key is not provided
     * @throws InvalidArgumentException if the key cannot be loaded
     */
    private static function decodePem(string $pem, array $matches, ?string $password = null): string
    {
        if (null === $password) {
            throw new InvalidArgumentException('Password required for encrypted keys.');
        }

        $iv = pack('H*', trim($matches[2]));
        $iv_sub = mb_substr($iv, 0, 8, '8bit');
        $symkey = pack('H*', md5($password.$iv_sub));
        $symkey .= pack('H*', md5($symkey.$password.$iv_sub));
        $key = preg_replace('#^(?:Proc-Type|DEK-Info): .*#m', '', $pem);
        $ciphertext = base64_decode(preg_replace('#-.*-|\r|\n#', '', $key), true);
        if (!is_string($ciphertext)) {
            throw new InvalidArgumentException('Unable to encode the data.');
        }

        $decoded = openssl_decrypt($ciphertext, mb_strtolower($matches[1]), $symkey, OPENSSL_RAW_DATA, $iv);
        if (false === $decoded) {
            throw new RuntimeException('Unable to decrypt the key');
        }
        $number = preg_match_all('#-{5}.*-{5}#', $pem, $result);
        if (2 !== $number) {
            throw new InvalidArgumentException('Unable to load the key');
        }

        $pem = $result[0][0].PHP_EOL;
        $pem .= chunk_split(base64_encode($decoded), 64);
        $pem .= $result[0][1].PHP_EOL;

        return $pem;
    }

    private static function convertDerToPem(string $der_data): string
    {
        $pem = chunk_split(base64_encode($der_data), 64, PHP_EOL);

        return '-----BEGIN CERTIFICATE-----'.PHP_EOL.$pem.'-----END CERTIFICATE-----'.PHP_EOL;
    }
}
