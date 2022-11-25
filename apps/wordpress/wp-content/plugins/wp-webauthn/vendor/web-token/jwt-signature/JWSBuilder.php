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

use function array_key_exists;
use Base64Url\Base64Url;
use function count;
use function in_array;
use InvalidArgumentException;
use function is_array;
use Jose\Component\Core\Algorithm;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\JsonConverter;
use Jose\Component\Core\Util\KeyChecker;
use Jose\Component\Signature\Algorithm\MacAlgorithm;
use Jose\Component\Signature\Algorithm\SignatureAlgorithm;
use LogicException;
use RuntimeException;

class JWSBuilder
{
    /**
     * @var null|string
     */
    protected $payload;

    /**
     * @var bool
     */
    protected $isPayloadDetached;

    /**
     * @var array
     */
    protected $signatures = [];

    /**
     * @var null|bool
     */
    protected $isPayloadEncoded;

    /**
     * @var AlgorithmManager
     */
    private $signatureAlgorithmManager;

    public function __construct(AlgorithmManager $signatureAlgorithmManager)
    {
        $this->signatureAlgorithmManager = $signatureAlgorithmManager;
    }

    /**
     * Returns the algorithm manager associated to the builder.
     */
    public function getSignatureAlgorithmManager(): AlgorithmManager
    {
        return $this->signatureAlgorithmManager;
    }

    /**
     * Reset the current data.
     *
     * @return JWSBuilder
     */
    public function create(): self
    {
        $this->payload = null;
        $this->isPayloadDetached = false;
        $this->signatures = [];
        $this->isPayloadEncoded = null;

        return $this;
    }

    /**
     * Set the payload.
     * This method will return a new JWSBuilder object.
     *
     * @throws InvalidArgumentException if the payload is not UTF-8 encoded
     *
     * @return JWSBuilder
     */
    public function withPayload(string $payload, bool $isPayloadDetached = false): self
    {
        if (false === mb_detect_encoding($payload, 'UTF-8', true)) {
            throw new InvalidArgumentException('The payload must be encoded in UTF-8');
        }
        $clone = clone $this;
        $clone->payload = $payload;
        $clone->isPayloadDetached = $isPayloadDetached;

        return $clone;
    }

    /**
     * Adds the information needed to compute the signature.
     * This method will return a new JWSBuilder object.
     *
     * @throws InvalidArgumentException if the payload encoding is inconsistent
     *
     * @return JWSBuilder
     */
    public function addSignature(JWK $signatureKey, array $protectedHeader, array $header = []): self
    {
        $this->checkB64AndCriticalHeader($protectedHeader);
        $isPayloadEncoded = $this->checkIfPayloadIsEncoded($protectedHeader);
        if (null === $this->isPayloadEncoded) {
            $this->isPayloadEncoded = $isPayloadEncoded;
        } elseif ($this->isPayloadEncoded !== $isPayloadEncoded) {
            throw new InvalidArgumentException('Foreign payload encoding detected.');
        }
        $this->checkDuplicatedHeaderParameters($protectedHeader, $header);
        KeyChecker::checkKeyUsage($signatureKey, 'signature');
        $algorithm = $this->findSignatureAlgorithm($signatureKey, $protectedHeader, $header);
        KeyChecker::checkKeyAlgorithm($signatureKey, $algorithm->name());
        $clone = clone $this;
        $clone->signatures[] = [
            'signature_algorithm' => $algorithm,
            'signature_key' => $signatureKey,
            'protected_header' => $protectedHeader,
            'header' => $header,
        ];

        return $clone;
    }

    /**
     * Computes all signatures and return the expected JWS object.
     *
     * @throws RuntimeException if the payload is not set
     * @throws RuntimeException if no signature is defined
     */
    public function build(): JWS
    {
        if (null === $this->payload) {
            throw new RuntimeException('The payload is not set.');
        }
        if (0 === count($this->signatures)) {
            throw new RuntimeException('At least one signature must be set.');
        }

        $encodedPayload = false === $this->isPayloadEncoded ? $this->payload : Base64Url::encode($this->payload);
        $jws = new JWS($this->payload, $encodedPayload, $this->isPayloadDetached);
        foreach ($this->signatures as $signature) {
            /** @var MacAlgorithm|SignatureAlgorithm $algorithm */
            $algorithm = $signature['signature_algorithm'];
            /** @var JWK $signatureKey */
            $signatureKey = $signature['signature_key'];
            /** @var array $protectedHeader */
            $protectedHeader = $signature['protected_header'];
            /** @var array $header */
            $header = $signature['header'];
            $encodedProtectedHeader = 0 === count($protectedHeader) ? null : Base64Url::encode(JsonConverter::encode($protectedHeader));
            $input = sprintf('%s.%s', $encodedProtectedHeader, $encodedPayload);
            if ($algorithm instanceof SignatureAlgorithm) {
                $s = $algorithm->sign($signatureKey, $input);
            } else {
                $s = $algorithm->hash($signatureKey, $input);
            }
            $jws = $jws->addSignature($s, $protectedHeader, $encodedProtectedHeader, $header);
        }

        return $jws;
    }

    private function checkIfPayloadIsEncoded(array $protectedHeader): bool
    {
        return !array_key_exists('b64', $protectedHeader) || true === $protectedHeader['b64'];
    }

    /**
     * @throws LogicException if the header parameter "crit" is missing, invalid or does not contain "b64" when "b64" is set
     */
    private function checkB64AndCriticalHeader(array $protectedHeader): void
    {
        if (!array_key_exists('b64', $protectedHeader)) {
            return;
        }
        if (!array_key_exists('crit', $protectedHeader)) {
            throw new LogicException('The protected header parameter "crit" is mandatory when protected header parameter "b64" is set.');
        }
        if (!is_array($protectedHeader['crit'])) {
            throw new LogicException('The protected header parameter "crit" must be an array.');
        }
        if (!in_array('b64', $protectedHeader['crit'], true)) {
            throw new LogicException('The protected header parameter "crit" must contain "b64" when protected header parameter "b64" is set.');
        }
    }

    /**
     * @throws InvalidArgumentException if the header parameter "alg" is missing or the algorithm is not allowed/not supported
     *
     * @return MacAlgorithm|SignatureAlgorithm
     */
    private function findSignatureAlgorithm(JWK $key, array $protectedHeader, array $header): Algorithm
    {
        $completeHeader = array_merge($header, $protectedHeader);
        if (!array_key_exists('alg', $completeHeader)) {
            throw new InvalidArgumentException('No "alg" parameter set in the header.');
        }
        if ($key->has('alg') && $key->get('alg') !== $completeHeader['alg']) {
            throw new InvalidArgumentException(sprintf('The algorithm "%s" is not allowed with this key.', $completeHeader['alg']));
        }

        $algorithm = $this->signatureAlgorithmManager->get($completeHeader['alg']);
        if (!$algorithm instanceof SignatureAlgorithm && !$algorithm instanceof MacAlgorithm) {
            throw new InvalidArgumentException(sprintf('The algorithm "%s" is not supported.', $completeHeader['alg']));
        }

        return $algorithm;
    }

    /**
     * @throws InvalidArgumentException if the header contains duplicated entries
     */
    private function checkDuplicatedHeaderParameters(array $header1, array $header2): void
    {
        $inter = array_intersect_key($header1, $header2);
        if (0 !== count($inter)) {
            throw new InvalidArgumentException(sprintf('The header contains duplicated entries: %s.', implode(', ', array_keys($inter))));
        }
    }
}
