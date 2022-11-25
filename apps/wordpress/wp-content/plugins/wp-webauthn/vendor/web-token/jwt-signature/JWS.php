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

use function count;
use InvalidArgumentException;
use Jose\Component\Core\JWT;

class JWS implements JWT
{
    /**
     * @var bool
     */
    private $isPayloadDetached = false;

    /**
     * @var null|string
     */
    private $encodedPayload;

    /**
     * @var Signature[]
     */
    private $signatures = [];

    /**
     * @var null|string
     */
    private $payload;

    public function __construct(?string $payload, ?string $encodedPayload = null, bool $isPayloadDetached = false)
    {
        $this->payload = $payload;
        $this->encodedPayload = $encodedPayload;
        $this->isPayloadDetached = $isPayloadDetached;
    }

    public function getPayload(): ?string
    {
        return $this->payload;
    }

    /**
     * Returns true if the payload is detached.
     */
    public function isPayloadDetached(): bool
    {
        return $this->isPayloadDetached;
    }

    /**
     * Returns the Base64Url encoded payload.
     * If the payload is detached, this method returns null.
     */
    public function getEncodedPayload(): ?string
    {
        if (true === $this->isPayloadDetached()) {
            return null;
        }

        return $this->encodedPayload;
    }

    /**
     * Returns the signatures associated with the JWS.
     *
     * @return Signature[]
     */
    public function getSignatures(): array
    {
        return $this->signatures;
    }

    /**
     * Returns the signature at the given index.
     *
     * @throws InvalidArgumentException if the signature index does not exist
     */
    public function getSignature(int $id): Signature
    {
        if (isset($this->signatures[$id])) {
            return $this->signatures[$id];
        }

        throw new InvalidArgumentException('The signature does not exist.');
    }

    /**
     * This method adds a signature to the JWS object.
     * Its returns a new JWS object.
     *
     * @internal
     *
     * @return JWS
     */
    public function addSignature(string $signature, array $protectedHeader, ?string $encodedProtectedHeader, array $header = []): self
    {
        $jws = clone $this;
        $jws->signatures[] = new Signature($signature, $protectedHeader, $encodedProtectedHeader, $header);

        return $jws;
    }

    /**
     * Returns the number of signature associated with the JWS.
     */
    public function countSignatures(): int
    {
        return count($this->signatures);
    }

    /**
     * This method splits the JWS into a list of JWSs.
     * It is only useful when the JWS contains more than one signature (JSON General Serialization).
     *
     * @return JWS[]
     */
    public function split(): array
    {
        $result = [];
        foreach ($this->signatures as $signature) {
            $jws = new self(
                $this->payload,
                $this->encodedPayload,
                $this->isPayloadDetached
            );
            $jws = $jws->addSignature(
                $signature->getSignature(),
                $signature->getProtectedHeader(),
                $signature->getEncodedProtectedHeader(),
                $signature->getHeader()
            );

            $result[] = $jws;
        }

        return $result;
    }
}
