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

use Jose\Component\Core\JWK;

interface KeyAnalyzer
{
    /**
     * This method will analyse the key and add messages to the message bag if needed.
     */
    public function analyze(JWK $jwk, MessageBag $bag): void;
}
