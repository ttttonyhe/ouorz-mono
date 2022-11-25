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

namespace CBOR;

use InvalidArgumentException;

abstract class Tag extends AbstractCBORObject
{
    private const MAJOR_TYPE = self::MAJOR_TYPE_TAG;

    /**
     * @var string|null
     */
    protected $data;

    /**
     * @var CBORObject
     */
    protected $object;

    public function __construct(int $additionalInformation, ?string $data, CBORObject $object)
    {
        parent::__construct(self::MAJOR_TYPE, $additionalInformation);
        $this->data = $data;
        $this->object = $object;
    }

    public function __toString(): string
    {
        $result = parent::__toString();
        if ($this->data !== null) {
            $result .= $this->data;
        }

        return $result . $this->object;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    abstract public static function getTagId(): int;

    abstract public static function createFromLoadedData(
        int $additionalInformation,
        ?string $data,
        CBORObject $object
    ): self;

    public function getValue(): CBORObject
    {
        return $this->object;
    }

    /**
     * @return array{int, null|string}
     */
    protected static function determineComponents(int $tag): array
    {
        switch (true) {
            case $tag < 0:
                throw new InvalidArgumentException('The value must be a positive integer.');
            case $tag < 24:
                return [$tag, null];
            case $tag < 0xFF:
                return [24, self::hex2bin(dechex($tag))];
            case $tag < 0xFFFF:
                return [25, self::hex2bin(dechex($tag))];
            case $tag < 0xFFFFFFFF:
                return [26, self::hex2bin(dechex($tag))];
            default:
                throw new InvalidArgumentException(
                    'Out of range. Please use PositiveBigIntegerTag tag with ByteStringObject object instead.'
                );
        }
    }

    private static function hex2bin(string $data): string
    {
        $result = hex2bin($data);
        if ($result === false) {
            throw new InvalidArgumentException('Unable to convert the data');
        }

        return $result;
    }
}
