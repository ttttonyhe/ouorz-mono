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

namespace CBOR\Tag;

use CBOR\CBORObject;
use CBOR\NegativeIntegerObject;
use CBOR\Normalizable;
use CBOR\OtherObject\DoublePrecisionFloatObject;
use CBOR\OtherObject\HalfPrecisionFloatObject;
use CBOR\OtherObject\SinglePrecisionFloatObject;
use CBOR\Tag;
use CBOR\UnsignedIntegerObject;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use const STR_PAD_RIGHT;

final class TimestampTag extends Tag implements Normalizable
{
    public function __construct(int $additionalInformation, ?string $data, CBORObject $object)
    {
        if (! $object instanceof UnsignedIntegerObject && ! $object instanceof NegativeIntegerObject && ! $object instanceof HalfPrecisionFloatObject && ! $object instanceof SinglePrecisionFloatObject && ! $object instanceof DoublePrecisionFloatObject) {
            throw new InvalidArgumentException('This tag only accepts integer-based or float-based objects.');
        }
        parent::__construct($additionalInformation, $data, $object);
    }

    public static function getTagId(): int
    {
        return self::TAG_EPOCH_DATETIME;
    }

    public static function createFromLoadedData(int $additionalInformation, ?string $data, CBORObject $object): Tag
    {
        return new self($additionalInformation, $data, $object);
    }

    public static function create(CBORObject $object): Tag
    {
        [$ai, $data] = self::determineComponents(self::TAG_EPOCH_DATETIME);

        return new self($ai, $data, $object);
    }

    public function normalize(): DateTimeInterface
    {
        $object = $this->object;

        switch (true) {
            case $object instanceof UnsignedIntegerObject:
            case $object instanceof NegativeIntegerObject:
                $formatted = DateTimeImmutable::createFromFormat('U', $object->normalize());

            break;
            case $object instanceof HalfPrecisionFloatObject:
            case $object instanceof SinglePrecisionFloatObject:
            case $object instanceof DoublePrecisionFloatObject:
                $value = (string) $object->normalize();
                $parts = explode('.', $value);
                if (isset($parts[1])) {
                    if (mb_strlen($parts[1], '8bit') > 6) {
                        $parts[1] = mb_substr($parts[1], 0, 6, '8bit');
                    } else {
                        $parts[1] = str_pad($parts[1], 6, '0', STR_PAD_RIGHT);
                    }
                }
                $formatted = DateTimeImmutable::createFromFormat('U.u', implode('.', $parts));

                break;
            default:
                throw new InvalidArgumentException('Unable to normalize the object');
        }

        if ($formatted === false) {
            throw new InvalidArgumentException('Invalid data. Cannot be converted into a datetime object');
        }

        return $formatted;
    }

    /**
     * @deprecated The method will be removed on v3.0. Please rely on the CBOR\Normalizable interface
     */
    public function getNormalizedData(bool $ignoreTags = false)
    {
        if ($ignoreTags) {
            return $this->object->getNormalizedData($ignoreTags);
        }
        switch (true) {
            case $this->object instanceof UnsignedIntegerObject:
            case $this->object instanceof NegativeIntegerObject:
            case $this->object instanceof HalfPrecisionFloatObject:
            case $this->object instanceof SinglePrecisionFloatObject:
            case $this->object instanceof DoublePrecisionFloatObject:
                return $this->normalize();
            default:
                return $this->object->getNormalizedData($ignoreTags);
        }
    }
}
