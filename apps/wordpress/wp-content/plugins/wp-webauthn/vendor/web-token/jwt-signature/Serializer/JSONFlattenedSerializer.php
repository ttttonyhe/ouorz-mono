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
use function is_array;
use Jose\Component\Core\Util\JsonConverter;
use Jose\Component\Signature\JWS;

final class JSONFlattenedSerializer extends Serializer
{
    public const NAME = 'jws_json_flattened';

    public function displayName(): string
    {
        return 'JWS JSON Flattened';
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function serialize(JWS $jws, ?int $signatureIndex = null): string
    {
        if (null === $signatureIndex) {
            $signatureIndex = 0;
        }
        $signature = $jws->getSignature($signatureIndex);

        $data = [];
        $values = [
            'payload' => $jws->getEncodedPayload(),
            'protected' => $signature->getEncodedProtectedHeader(),
            'header' => $signature->getHeader(),
        ];
        $encodedPayload = $jws->getEncodedPayload();
        if (null !== $encodedPayload && '' !== $encodedPayload) {
            $data['payload'] = $encodedPayload;
        }
        $encodedProtectedHeader = $signature->getEncodedProtectedHeader();
        if (null !== $encodedProtectedHeader && '' !== $encodedProtectedHeader) {
            $data['protected'] = $encodedProtectedHeader;
        }
        $header = $signature->getHeader();
        if (0 !== count($header)) {
            $data['header'] = $header;
        }
        $data['signature'] = Base64Url::encode($signature->getSignature());

        return JsonConverter::encode($data);
    }

    /**
     * @throws InvalidArgumentException if the input is not supported
     * @throws InvalidArgumentException if the JWS header is invalid
     */
    public function unserialize(string $input): JWS
    {
        $data = JsonConverter::decode($input);
        if (!is_array($data)) {
            throw new InvalidArgumentException('Unsupported input.');
        }
        if (!isset($data['signature'])) {
            throw new InvalidArgumentException('Unsupported input.');
        }
        $signature = Base64Url::decode($data['signature']);

        if (isset($data['protected'])) {
            $encodedProtectedHeader = $data['protected'];
            $protectedHeader = JsonConverter::decode(Base64Url::decode($data['protected']));
        } else {
            $encodedProtectedHeader = null;
            $protectedHeader = [];
        }
        if (isset($data['header'])) {
            if (!is_array($data['header'])) {
                throw new InvalidArgumentException('Bad header.');
            }
            $header = $data['header'];
        } else {
            $header = [];
        }

        if (isset($data['payload'])) {
            $encodedPayload = $data['payload'];
            $payload = $this->isPayloadEncoded($protectedHeader) ? Base64Url::decode($encodedPayload) : $encodedPayload;
        } else {
            $payload = null;
            $encodedPayload = null;
        }

        $jws = new JWS($payload, $encodedPayload, null === $encodedPayload);

        return $jws->addSignature($signature, $protectedHeader, $encodedProtectedHeader, $header);
    }
}
