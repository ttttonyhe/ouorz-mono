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

final class NoneAnalyzer implements KeyAnalyzer
{
    public function analyze(JWK $jwk, MessageBag $bag): void
    {
        if ('none' !== $jwk->get('kty')) {
            return;
        }

        $bag->add(Message::high('This key is a meant to be used with the algorithm "none". This algorithm is not secured and should be used with care.'));
    }
}
