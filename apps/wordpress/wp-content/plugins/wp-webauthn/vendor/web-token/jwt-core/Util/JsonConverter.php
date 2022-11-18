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

namespace Jose\Component\Core\Util;

use InvalidArgumentException;
use function is_string;
use RuntimeException;
use Throwable;

final class JsonConverter
{
    /**
     * @param mixed $payload
     *
     * @throws RuntimeException if the payload cannot be encoded
     */
    public static function encode($payload): string
    {
        try {
            $data = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if (!is_string($data)) {
                throw new InvalidArgumentException('Unable to encode the data');
            }

            return $data;
        } catch (Throwable $throwable) {
            throw new RuntimeException('Invalid content.', $throwable->getCode(), $throwable);
        }
    }

    /**
     * @throws RuntimeException if the payload cannot be decoded
     *
     * @return mixed
     */
    public static function decode(string $payload)
    {
        try {
            return json_decode($payload, true, 512, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (Throwable $throwable) {
            throw new RuntimeException('Invalid content.', $throwable->getCode(), $throwable);
        }
    }
}
