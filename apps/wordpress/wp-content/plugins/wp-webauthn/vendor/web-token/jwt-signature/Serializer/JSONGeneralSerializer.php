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

use function array_key_exists;
use Base64Url\Base64Url;
use function count;
use InvalidArgumentException;
use function is_array;
use function is_string;
use Jose\Component\Core\Util\JsonConverter;
use Jose\Component\Signature\JWS;
use LogicException;

final class JSONGeneralSerializer extends Serializer
{
    public const NAME = 'jws_json_general';

    public function displayName(): string
    {
        return 'JWS JSON General';
    }

    public function name(): string
    {
        return self::NAME;
    }

    /**
     * @throws LogicException if no signature is attached
     */
    public function serialize(JWS $jws, ?int $signatureIndex = null): string
    {
        if (0 === $jws->countSignatures()) {
            throw new LogicException('No signature.');
        }

        $data = [];
        $this->checkPayloadEncoding($jws);

        if (false === $jws->isPayloadDetached()) {
            $data['payload'] = $jws->getEncodedPayload();
        }

        $data['signatures'] = [];
        foreach ($jws->getSignatures() as $signature) {
            $tmp = ['signature' => Base64Url::encode($signature->getSignature())];
            $values = [
                'protected' => $signature->getEncodedProtectedHeader(),
                'header' => $signature->getHeader(),
            ];

            foreach ($values as $key => $value) {
                if ((is_string($value) && '' !== $value) || (is_array($value) && 0 !== count($value))) {
                    $tmp[$key] = $value;
                }
            }
            $data['signatures'][] = $tmp;
        }

        return JsonConverter::encode($data);
    }

    /**
     * @throws InvalidArgumentException if the input is not supported
     */
    public function unserialize(string $input): JWS
    {
        $data = JsonConverter::decode($input);
        if (!isset($data['signatures'])) {
            throw new InvalidArgumentException('Unsupported input.');
        }

        $isPayloadEncoded = null;
        $rawPayload = $data['payload'] ?? null;
        $signatures = [];
        foreach ($data['signatures'] as $signature) {
            if (!isset($signature['signature'])) {
                throw new InvalidArgumentException('Unsupported input.');
            }
            [$encodedProtectedHeader, $protectedHeader, $header] = $this->processHeaders($signature);
            $signatures[] = [
                'signature' => Base64Url::decode($signature['signature']),
                'protected' => $protectedHeader,
                'encoded_protected' => $encodedProtectedHeader,
                'header' => $header,
            ];
            $isPayloadEncoded = $this->processIsPayloadEncoded($isPayloadEncoded, $protectedHeader);
        }

        $payload = $this->processPayload($rawPayload, $isPayloadEncoded);
        $jws = new JWS($payload, $rawPayload);
        foreach ($signatures as $signature) {
            $jws = $jws->addSignature(
                $signature['signature'],
                $signature['protected'],
                $signature['encoded_protected'],
                $signature['header']
            );
        }

        return $jws;
    }

    /**
     * @throws InvalidArgumentException if the payload encoding is invalid
     */
    private function processIsPayloadEncoded(?bool $isPayloadEncoded, array $protectedHeader): bool
    {
        if (null === $isPayloadEncoded) {
            return $this->isPayloadEncoded($protectedHeader);
        }
        if ($this->isPayloadEncoded($protectedHeader) !== $isPayloadEncoded) {
            throw new InvalidArgumentException('Foreign payload encoding detected.');
        }

        return $isPayloadEncoded;
    }

    private function processHeaders(array $signature): array
    {
        $encodedProtectedHeader = $signature['protected'] ?? null;
        $protectedHeader = null === $encodedProtectedHeader ? [] : JsonConverter::decode(Base64Url::decode($encodedProtectedHeader));
        $header = array_key_exists('header', $signature) ? $signature['header'] : [];

        return [$encodedProtectedHeader, $protectedHeader, $header];
    }

    private function processPayload(?string $rawPayload, ?bool $isPayloadEncoded): ?string
    {
        if (null === $rawPayload) {
            return null;
        }

        return false === $isPayloadEncoded ? $rawPayload : Base64Url::decode($rawPayload);
    }

    // @throws LogicException if the payload encoding is invalid
    private function checkPayloadEncoding(JWS $jws): void
    {
        if ($jws->isPayloadDetached()) {
            return;
        }
        $is_encoded = null;
        foreach ($jws->getSignatures() as $signature) {
            if (null === $is_encoded) {
                $is_encoded = $this->isPayloadEncoded($signature->getProtectedHeader());
            }
            if (false === $jws->isPayloadDetached()) {
                if ($is_encoded !== $this->isPayloadEncoded($signature->getProtectedHeader())) {
                    throw new LogicException('Foreign payload encoding detected.');
                }
            }
        }
    }
}
