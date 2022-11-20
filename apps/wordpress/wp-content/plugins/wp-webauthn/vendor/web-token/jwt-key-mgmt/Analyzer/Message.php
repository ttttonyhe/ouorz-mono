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

namespace Jose\Component\KeyManagement\Analyzer;

use JsonSerializable;

class Message implements JsonSerializable
{
    public const SEVERITY_LOW = 'low';

    public const SEVERITY_MEDIUM = 'medium';

    public const SEVERITY_HIGH = 'high';
    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $severity;

    /**
     * Message constructor.
     */
    private function __construct(string $message, string $severity)
    {
        $this->message = $message;
        $this->severity = $severity;
    }

    /**
     * Creates a message with severity=low.
     *
     * @return Message
     */
    public static function low(string $message): self
    {
        return new self($message, self::SEVERITY_LOW);
    }

    /**
     * Creates a message with severity=medium.
     *
     * @return Message
     */
    public static function medium(string $message): self
    {
        return new self($message, self::SEVERITY_MEDIUM);
    }

    /**
     * Creates a message with severity=high.
     *
     * @return Message
     */
    public static function high(string $message): self
    {
        return new self($message, self::SEVERITY_HIGH);
    }

    /**
     * Returns the message.
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Returns the severity of the message.
     */
    public function getSeverity(): string
    {
        return $this->severity;
    }

    public function jsonSerialize(): array
    {
        return [
            'message' => $this->message,
            'severity' => $this->severity,
        ];
    }
}
