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

use CBOR\OtherObject as Base;
use InvalidArgumentException;
use function ord;

final class GenericObject extends Base
{
    public static function supportedAdditionalInformation(): array
    {
        return [];
    }

    public static function createFromLoadedData(int $additionalInformation, ?string $data): Base
    {
        if ($data !== null && ord($data) < 32) {
            throw new InvalidArgumentException('Invalid simple value. Content data should not be present.');
        }

        return new self($additionalInformation, $data);
    }

    /**
     * @deprecated The method will be removed on v3.0. Please rely on the CBOR\Normalizable interface
     */
    public function getNormalizedData(bool $ignoreTags = false)
    {
        return $this->data;
    }
}
