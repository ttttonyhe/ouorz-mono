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

namespace Jose\Component\Signature\Algorithm;

use function in_array;
use InvalidArgumentException;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\RSAKey;
use RuntimeException;

abstract class RSAPKCS1 implements SignatureAlgorithm
{
    public function allowedKeyTypes(): array
    {
        return ['RSA'];
    }

    public function verify(JWK $key, string $input, string $signature): bool
    {
        $this->checkKey($key);
        $pub = RSAKey::createFromJWK($key->toPublic());

        return 1 === openssl_verify($input, $signature, $pub->toPEM(), $this->getAlgorithm());
    }

    /**
     * @throws InvalidArgumentException if the key is not private
     * @throws InvalidArgumentException if the data cannot be signed
     */
    public function sign(JWK $key, string $input): string
    {
        $this->checkKey($key);
        if (!$key->has('d')) {
            throw new InvalidArgumentException('The key is not a private key.');
        }

        $priv = RSAKey::createFromJWK($key);

        $result = openssl_sign($input, $signature, $priv->toPEM(), $this->getAlgorithm());
        if (true !== $result) {
            throw new RuntimeException('Unable to sign');
        }

        return $signature;
    }

    abstract protected function getAlgorithm(): string;

    /**
     * @throws InvalidArgumentException if the key type is not allowed
     * @throws InvalidArgumentException if the key is not valid
     */
    private function checkKey(JWK $key): void
    {
        if (!in_array($key->get('kty'), $this->allowedKeyTypes(), true)) {
            throw new InvalidArgumentException('Wrong key type.');
        }
        foreach (['n', 'e'] as $k) {
            if (!$key->has($k)) {
                throw new InvalidArgumentException(sprintf('The key parameter "%s" is missing.', $k));
            }
        }
    }
}
