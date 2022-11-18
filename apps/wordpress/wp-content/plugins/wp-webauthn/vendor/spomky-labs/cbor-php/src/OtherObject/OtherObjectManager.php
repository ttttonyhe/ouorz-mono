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

use function array_key_exists;
use CBOR\OtherObject;
use InvalidArgumentException;

/**
 * @final
 */
class OtherObjectManager implements OtherObjectManagerInterface
{
    /**
     * @var string[]
     */
    private $classes = [];

    public static function create(): self
    {
        return new self();
    }

    public function add(string $class): self
    {
        foreach ($class::supportedAdditionalInformation() as $ai) {
            if ($ai < 0) {
                throw new InvalidArgumentException('Invalid additional information.');
            }
            $this->classes[$ai] = $class;
        }

        return $this;
    }

    public function getClassForValue(int $value): string
    {
        return array_key_exists($value, $this->classes) ? $this->classes[$value] : GenericObject::class;
    }

    public function createObjectForValue(int $value, ?string $data): OtherObject
    {
        /** @var OtherObject $class */
        $class = $this->getClassForValue($value);

        return $class::createFromLoadedData($value, $data);
    }
}
