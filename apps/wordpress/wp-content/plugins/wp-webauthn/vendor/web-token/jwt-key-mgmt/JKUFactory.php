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

namespace Jose\Component\KeyManagement;

use function is_array;
use Jose\Component\Core\JWKSet;
use Jose\Component\Core\Util\JsonConverter;
use RuntimeException;

class JKUFactory extends UrlKeySetFactory
{
    /**
     * This method will try to fetch the url a retrieve the key set.
     * Throws an exception in case of failure.
     *
     * @throws RuntimeException if the key cannot be reached
     */
    public function loadFromUrl(string $url, array $header = []): JWKSet
    {
        $content = $this->getContent($url, $header);
        $data = JsonConverter::decode($content);
        if (!is_array($data)) {
            throw new RuntimeException('Invalid content.');
        }

        return JWKSet::createFromKeyData($data);
    }
}
