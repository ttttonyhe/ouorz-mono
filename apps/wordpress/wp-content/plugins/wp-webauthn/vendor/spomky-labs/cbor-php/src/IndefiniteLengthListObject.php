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

use function array_key_exists;
use ArrayAccess;
use ArrayIterator;
use function count;
use Countable;
use InvalidArgumentException;
use Iterator;
use IteratorAggregate;

/**
 * @phpstan-implements ArrayAccess<int, CBORObject>
 * @phpstan-implements IteratorAggregate<int, CBORObject>
 * @final
 */
class IndefiniteLengthListObject extends AbstractCBORObject implements Countable, IteratorAggregate, Normalizable, ArrayAccess
{
    private const MAJOR_TYPE = self::MAJOR_TYPE_LIST;

    private const ADDITIONAL_INFORMATION = self::LENGTH_INDEFINITE;

    /**
     * @var CBORObject[]
     */
    private $data = [];

    public function __construct()
    {
        parent::__construct(self::MAJOR_TYPE, self::ADDITIONAL_INFORMATION);
    }

    public function __toString(): string
    {
        $result = parent::__toString();
        foreach ($this->data as $object) {
            $result .= (string) $object;
        }

        return $result . "\xFF";
    }

    public static function create(): self
    {
        return new self();
    }

    /**
     * @return mixed[]
     */
    public function normalize(): array
    {
        return array_map(static function (CBORObject $object) {
            return $object instanceof Normalizable ? $object->normalize() : $object;
        }, $this->data);
    }

    /**
     * @deprecated The method will be removed on v3.0. Please rely on the CBOR\Normalizable interface
     *
     * @return mixed[]
     */
    public function getNormalizedData(bool $ignoreTags = false): array
    {
        return array_map(static function (CBORObject $object) use ($ignoreTags) {
            return $object->getNormalizedData($ignoreTags);
        }, $this->data);
    }

    public function add(CBORObject $item): self
    {
        $this->data[] = $item;

        return $this;
    }

    public function has(int $index): bool
    {
        return array_key_exists($index, $this->data);
    }

    public function remove(int $index): self
    {
        if (! $this->has($index)) {
            return $this;
        }
        unset($this->data[$index]);
        $this->data = array_values($this->data);

        return $this;
    }

    public function get(int $index): CBORObject
    {
        if (! $this->has($index)) {
            throw new InvalidArgumentException('Index not found.');
        }

        return $this->data[$index];
    }

    public function set(int $index, CBORObject $object): self
    {
        if (! $this->has($index)) {
            throw new InvalidArgumentException('Index not found.');
        }

        $this->data[$index] = $object;

        return $this;
    }

    /**
     * @deprecated The method will be removed on v3.0. No replacement
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * @return Iterator<int, CBORObject>
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->data);
    }

    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    public function offsetGet($offset): CBORObject
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        if ($offset === null) {
            $this->add($value);

            return;
        }

        $this->set($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        $this->remove($offset);
    }
}
