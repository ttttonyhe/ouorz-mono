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
use Jose\Component\Signature\Algorithm\Util\RSA as JoseRSA;

/**
 * @deprecated Please use either RSAPSS or RSAPKCS1 depending on the padding mode
 */
abstract class RSA implements SignatureAlgorithm
{
    public function allowedKeyTypes(): array
    {
        return ['RSA'];
    }

    public function verify(JWK $key, string $input, string $signature): bool
    {
        $this->checkKey($key);
        $pub = RSAKey::createFromJWK($key->toPublic());

        return JoseRSA::verify($pub, $input, $signature, $this->getAlgorithm(), $this->getSignatureMethod());
    }

    /**
     * @throws InvalidArgumentException if the key is not private
     */
    public function sign(JWK $key, string $input): string
    {
        $this->checkKey($key);
        if (!$key->has('d')) {
            throw new InvalidArgumentException('The key is not a private key.');
        }

        $priv = RSAKey::createFromJWK($key);

        return JoseRSA::sign($priv, $input, $this->getAlgorithm(), $this->getSignatureMethod());
    }

    abstract protected function getAlgorithm(): string;

    abstract protected function getSignatureMethod(): int;

    /**
     * @throws InvalidArgumentException if the key type is not allowed
     * @throws InvalidArgumentException if the key is invalid
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
