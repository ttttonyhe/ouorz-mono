<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2018-2020 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace CBOR\OtherObject;

use CBOR\Normalizable;
use CBOR\OtherObject as Base;
use CBOR\Utils;
use function chr;
use InvalidArgumentException;
use function ord;

final class SimpleObject extends Base implements Normalizable
{
    public static function supportedAdditionalInformation(): array
    {
        return array_merge(range(0, 19), [24]);
    }

    public static function createFromLoadedData(int $additionalInformation, ?string $data): Base
    {
        if ($additionalInformation === 24) {
            if ($data === null) {
                throw new InvalidArgumentException('Invalid simple value. Content data is missing.');
            }
            if (mb_strlen($data, '8bit') !== 1) {
                throw new InvalidArgumentException('Invalid simple value. Content data is too long.');
            }
            if (ord($data) < 32) {
                throw new InvalidArgumentException('Invalid simple value. Content data must be between 32 and 255.');
            }
        } elseif ($additionalInformation < 20) {
            if ($data !== null) {
                throw new InvalidArgumentException('Invalid simple value. Content data should not be present.');
            }
        }

        return new self($additionalInformation, $data);
    }

    public function normalize(): int
    {
        if ($this->data === null) {
            return $this->getAdditionalInformation();
        }

        return Utils::binToInt($this->data);
    }

    /**
     * @deprecated The method will be removed on v3.0. Please rely on the CBOR\Normalizable interface
     */
    public function getNormalizedData(bool $ignoreTags = false): int
    {
        return $this->normalize();
    }

    public static function create(int $value): self
    {
        switch (true) {
            case $value < 32:
                return new self($value, null);
            case $value < 256:
                return new self(24, chr($value));
            default:
                throw new InvalidArgumentException('The value is not a valid simple value.');
        }
    }
}
