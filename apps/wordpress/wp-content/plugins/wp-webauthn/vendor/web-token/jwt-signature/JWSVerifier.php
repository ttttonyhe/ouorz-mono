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

use Base64Url\Base64Url;
use InvalidArgumentException;
use Jose\Component\Core\Algorithm;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWK;
use Jose\Component\Core\JWKSet;
use Jose\Component\Core\Util\KeyChecker;
use Jose\Component\Signature\Algorithm\MacAlgorithm;
use Jose\Component\Signature\Algorithm\SignatureAlgorithm;
use Throwable;

class JWSVerifier
{
    /**
     * @var AlgorithmManager
     */
    private $signatureAlgorithmManager;

    /**
     * JWSVerifier constructor.
     */
    public function __construct(AlgorithmManager $signatureAlgorithmManager)
    {
        $this->signatureAlgorithmManager = $signatureAlgorithmManager;
    }

    /**
     * Returns the algorithm manager associated to the JWSVerifier.
     */
    public function getSignatureAlgorithmManager(): AlgorithmManager
    {
        return $this->signatureAlgorithmManager;
    }

    /**
     * This method will try to verify the JWS object using the given key and for the given signature.
     * It returns true if the signature is verified, otherwise false.
     *
     * @return bool true if the verification of the signature succeeded, else false
     */
    public function verifyWithKey(JWS $jws, JWK $jwk, int $signature, ?string $detachedPayload = null): bool
    {
        $jwkset = new JWKSet([$jwk]);

        return $this->verifyWithKeySet($jws, $jwkset, $signature, $detachedPayload);
    }

    /**
     * This method will try to verify the JWS object using the given key set and for the given signature.
     * It returns true if the signature is verified, otherwise false.
     *
     * @param JWS         $jws             A JWS object
     * @param JWKSet      $jwkset          The signature will be verified using keys in the key set
     * @param JWK         $jwk             The key used to verify the signature in case of success
     * @param null|string $detachedPayload If not null, the value must be the detached payload encoded in Base64 URL safe. If the input contains a payload, throws an exception.
     *
     * @throws InvalidArgumentException if there is no key in the keyset
     * @throws InvalidArgumentException if the token does not contain any signature
     *
     * @return bool true if the verification of the signature succeeded, else false
     */
    public function verifyWithKeySet(JWS $jws, JWKSet $jwkset, int $signatureIndex, ?string $detachedPayload = null, JWK &$jwk = null): bool
    {
        if (0 === $jwkset->count()) {
            throw new InvalidArgumentException('There is no key in the key set.');
        }
        if (0 === $jws->countSignatures()) {
            throw new InvalidArgumentException('The JWS does not contain any signature.');
        }
        $this->checkPayload($jws, $detachedPayload);
        $signature = $jws->getSignature($signatureIndex);

        return $this->verifySignature($jws, $jwkset, $signature, $detachedPayload, $jwk);
    }

    private function verifySignature(JWS $jws, JWKSet $jwkset, Signature $signature, ?string $detachedPayload = null, JWK &$successJwk = null): bool
    {
        $input = $this->getInputToVerify($jws, $signature, $detachedPayload);
        $algorithm = $this->getAlgorithm($signature);
        foreach ($jwkset->all() as $jwk) {
            try {
                KeyChecker::checkKeyUsage($jwk, 'verification');
                KeyChecker::checkKeyAlgorithm($jwk, $algorithm->name());
                if (true === $algorithm->verify($jwk, $input, $signature->getSignature())) {
                    $successJwk = $jwk;

                    return true;
                }
            } catch (Throwable $e) {
                //We do nothing, we continue with other keys
                continue;
            }
        }

        return false;
    }

    private function getInputToVerify(JWS $jws, Signature $signature, ?string $detachedPayload): string
    {
        $isPayloadEmpty = $this->isPayloadEmpty($jws->getPayload());
        $encodedProtectedHeader = $signature->getEncodedProtectedHeader();
        if (!$signature->hasProtectedHeaderParameter('b64') || true === $signature->getProtectedHeaderParameter('b64')) {
            if (null !== $jws->getEncodedPayload()) {
                return sprintf('%s.%s', $encodedProtectedHeader, $jws->getEncodedPayload());
            }

            $payload = $isPayloadEmpty ? $detachedPayload : $jws->getPayload();

            return sprintf('%s.%s', $encodedProtectedHeader, Base64Url::encode($payload));
        }

        $payload = $isPayloadEmpty ? $detachedPayload : $jws->getPayload();

        return sprintf('%s.%s', $encodedProtectedHeader, $payload);
    }

    /**
     * @throws InvalidArgumentException if the payload is set when a detached payload is provided or no payload is defined
     */
    private function checkPayload(JWS $jws, ?string $detachedPayload = null): void
    {
        $isPayloadEmpty = $this->isPayloadEmpty($jws->getPayload());
        if (null !== $detachedPayload && !$isPayloadEmpty) {
            throw new InvalidArgumentException('A detached payload is set, but the JWS already has a payload.');
        }
        if ($isPayloadEmpty && null === $detachedPayload) {
            throw new InvalidArgumentException('The JWS has a detached payload, but no payload is provided.');
        }
    }

    /**
     * @throws InvalidArgumentException if the header parameter "alg" is missing or invalid
     *
     * @return MacAlgorithm|SignatureAlgorithm
     */
    private function getAlgorithm(Signature $signature): Algorithm
    {
        $completeHeader = array_merge($signature->getProtectedHeader(), $signature->getHeader());
        if (!isset($completeHeader['alg'])) {
            throw new InvalidArgumentException('No "alg" parameter set in the header.');
        }

        $algorithm = $this->signatureAlgorithmManager->get($completeHeader['alg']);
        if (!$algorithm instanceof SignatureAlgorithm && !$algorithm instanceof MacAlgorithm) {
            throw new InvalidArgumentException(sprintf('The algorithm "%s" is not supported or is not a signature or MAC algorithm.', $completeHeader['alg']));
        }

        return $algorithm;
    }

    private function isPayloadEmpty(?string $payload): bool
    {
        return null === $payload || '' === $payload;
    }
}
