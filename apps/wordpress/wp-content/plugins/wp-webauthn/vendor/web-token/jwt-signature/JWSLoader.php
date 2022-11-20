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

namespace Jose\Component\Signature;

use Exception;
use Jose\Component\Checker\HeaderCheckerManager;
use Jose\Component\Core\JWK;
use Jose\Component\Core\JWKSet;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Throwable;

class JWSLoader
{
    /**
     * @var JWSVerifier
     */
    private $jwsVerifier;

    /**
     * @var null|HeaderCheckerManager
     */
    private $headerCheckerManager;

    /**
     * @var JWSSerializerManager
     */
    private $serializerManager;

    /**
     * JWSLoader constructor.
     */
    public function __construct(JWSSerializerManager $serializerManager, JWSVerifier $jwsVerifier, ?HeaderCheckerManager $headerCheckerManager)
    {
        $this->serializerManager = $serializerManager;
        $this->jwsVerifier = $jwsVerifier;
        $this->headerCheckerManager = $headerCheckerManager;
    }

    /**
     * Returns the JWSVerifier associated to the JWSLoader.
     */
    public function getJwsVerifier(): JWSVerifier
    {
        return $this->jwsVerifier;
    }

    /**
     * Returns the Header Checker Manager associated to the JWSLoader.
     */
    public function getHeaderCheckerManager(): ?HeaderCheckerManager
    {
        return $this->headerCheckerManager;
    }

    /**
     * Returns the JWSSerializer associated to the JWSLoader.
     */
    public function getSerializerManager(): JWSSerializerManager
    {
        return $this->serializerManager;
    }

    /**
     * This method will try to load and verify the token using the given key.
     * It returns a JWS and will populate the $signature variable in case of success, otherwise an exception is thrown.
     *
     * @throws Exception if the token cannot be loaded or verified
     */
    public function loadAndVerifyWithKey(string $token, JWK $key, ?int &$signature, ?string $payload = null): JWS
    {
        $keyset = new JWKSet([$key]);

        return $this->loadAndVerifyWithKeySet($token, $keyset, $signature, $payload);
    }

    /**
     * This method will try to load and verify the token using the given key set.
     * It returns a JWS and will populate the $signature variable in case of success, otherwise an exception is thrown.
     *
     * @throws Exception if the token cannot be loaded or verified
     */
    public function loadAndVerifyWithKeySet(string $token, JWKSet $keyset, ?int &$signature, ?string $payload = null): JWS
    {
        try {
            $jws = $this->serializerManager->unserialize($token);
            $nbSignatures = $jws->countSignatures();
            for ($i = 0; $i < $nbSignatures; ++$i) {
                if ($this->processSignature($jws, $keyset, $i, $payload)) {
                    $signature = $i;

                    return $jws;
                }
            }
        } catch (Throwable $e) {
            // Nothing to do. Exception thrown just after
        }

        throw new Exception('Unable to load and verify the token.');
    }

    private function processSignature(JWS $jws, JWKSet $keyset, int $signature, ?string $payload): bool
    {
        try {
            if (null !== $this->headerCheckerManager) {
                $this->headerCheckerManager->check($jws, $signature);
            }

            return $this->jwsVerifier->verifyWithKeySet($jws, $keyset, $signature, $payload);
        } catch (Throwable $e) {
            return false;
        }
    }
}
