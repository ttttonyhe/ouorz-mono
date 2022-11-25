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

use InvalidArgumentException;
use Jose\Component\Signature\JWS;

class JWSSerializerManager
{
    /**
     * @var JWSSerializer[]
     */
    private $serializers = [];

    /**
     * @param JWSSerializer[] $serializers
     */
    public function __construct(array $serializers)
    {
        foreach ($serializers as $serializer) {
            $this->add($serializer);
        }
    }

    /**
     * @return string[]
     */
    public function list(): array
    {
        return array_keys($this->serializers);
    }

    /**
     * Converts a JWS into a string.
     *
     * @throws InvalidArgumentException if the serializer is not supported
     */
    public function serialize(string $name, JWS $jws, ?int $signatureIndex = null): string
    {
        if (!isset($this->serializers[$name])) {
            throw new InvalidArgumentException(sprintf('Unsupported serializer "%s".', $name));
        }

        return $this->serializers[$name]->serialize($jws, $signatureIndex);
    }

    /**
     * Loads data and return a JWS object.
     *
     * @param string      $input A string that represents a JWS
     * @param null|string $name  the name of the serializer if the input is unserialized
     *
     * @throws InvalidArgumentException if the input is not supported
     */
    public function unserialize(string $input, ?string &$name = null): JWS
    {
        foreach ($this->serializers as $serializer) {
            try {
                $jws = $serializer->unserialize($input);
                $name = $serializer->name();

                return $jws;
            } catch (InvalidArgumentException $e) {
                continue;
            }
        }

        throw new InvalidArgumentException('Unsupported input.');
    }

    private function add(JWSSerializer $serializer): void
    {
        $this->serializers[$serializer->name()] = $serializer;
    }
}
