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

namespace Jose\Component\Signature\Serializer;

use Base64Url\Base64Url;
use function count;
use InvalidArgumentException;
use Jose\Component\Core\Util\JsonConverter;
use Jose\Component\Signature\JWS;
use LogicException;
use Throwable;

final class CompactSerializer extends Serializer
{
    public const NAME = 'jws_compact';

    public function displayName(): string
    {
        return 'JWS Compact';
    }

    public function name(): string
    {
        return self::NAME;
    }

    /**
     * @throws LogicException if the JWS has unprotected header (invalid for compact JSON)
     * @throws LogicException if the payload is not encoded but contains unauthorized characters
     */
    public function serialize(JWS $jws, ?int $signatureIndex = null): string
    {
        if (null === $signatureIndex) {
            $signatureIndex = 0;
        }
        $signature = $jws->getSignature($signatureIndex);
        if (0 !== count($signature->getHeader())) {
            throw new LogicException('The signature contains unprotected header parameters and cannot be converted into compact JSON.');
        }
        $isEmptyPayload = null === $jws->getEncodedPayload() || '' === $jws->getEncodedPayload();
        if (!$this->isPayloadEncoded($signature->getProtectedHeader()) && !$isEmptyPayload) {
            if (1 !== preg_match('/^[\x{20}-\x{2d}|\x{2f}-\x{7e}]*$/u', $jws->getPayload())) {
                throw new LogicException('Unable to convert the JWS with non-encoded payload.');
            }
        }

        return sprintf(
            '%s.%s.%s',
            $signature->getEncodedProtectedHeader(),
            $jws->getEncodedPayload(),
            Base64Url::encode($signature->getSignature())
        );
    }

    /**
     * @throws InvalidArgumentException if the input is invalid
     */
    public function unserialize(string $input): JWS
    {
        $parts = explode('.', $input);
        if (3 !== count($parts)) {
            throw new InvalidArgumentException('Unsupported input');
        }

        try {
            $encodedProtectedHeader = $parts[0];
            $protectedHeader = JsonConverter::decode(Base64Url::decode($parts[0]));
            $hasPayload = '' !== $parts[1];
            if (!$hasPayload) {
                $payload = null;
                $encodedPayload = null;
            } else {
                $encodedPayload = $parts[1];
                $payload = $this->isPayloadEncoded($protectedHeader) ? Base64Url::decode($encodedPayload) : $encodedPayload;
            }
            $signature = Base64Url::decode($parts[2]);

            $jws = new JWS($payload, $encodedPayload, !$hasPayload);

            return $jws->addSignature($signature, $protectedHeader, $encodedProtectedHeader);
        } catch (Throwable $throwable) {
            throw new InvalidArgumentException('Unsupported input', $throwable->getCode(), $throwable);
        }
    }
}
