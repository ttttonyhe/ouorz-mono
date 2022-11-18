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

use Jose\Component\Core\JWKSet;

final class MixedPublicAndPrivateKeys implements KeysetAnalyzer
{
    public function analyze(JWKSet $jwkset, MessageBag $bag): void
    {
        if (0 === $jwkset->count()) {
            return;
        }

        $hasPublicKeys = false;
        $hasPrivateKeys = false;

        foreach ($jwkset as $jwk) {
            switch ($jwk->get('kty')) {
                case 'OKP':
                case 'RSA':
                case 'EC':
                    if ($jwk->has('d')) {
                        $hasPrivateKeys = true;
                    } else {
                        $hasPublicKeys = true;
                    }

                    break;
            }
        }

        if ($hasPrivateKeys && $hasPublicKeys) {
            $bag->add(Message::high('This key set mixes public and private keys.'));
        }
    }
}
