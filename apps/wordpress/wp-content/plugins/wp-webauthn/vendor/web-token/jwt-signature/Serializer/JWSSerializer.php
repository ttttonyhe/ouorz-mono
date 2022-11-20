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

use Jose\Component\Signature\JWS;

interface JWSSerializer
{
    /**
     * The name of the serialization.
     */
    public function name(): string;

    public function displayName(): string;

    /**
     * Converts a JWS into a string.
     */
    public function serialize(JWS $jws, ?int $signatureIndex = null): string;

    /**
     * Loads data and return a JWS object.
     *
     * @param string $input A string that represents a JWS
     */
    public function unserialize(string $input): JWS;
}
