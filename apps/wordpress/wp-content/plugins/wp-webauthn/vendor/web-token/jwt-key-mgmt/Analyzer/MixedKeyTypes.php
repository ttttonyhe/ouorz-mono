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

final class MixedKeyTypes implements KeysetAnalyzer
{
    public function analyze(JWKSet $jwkset, MessageBag $bag): void
    {
        if (0 === $jwkset->count()) {
            return;
        }

        $hasSymmetricKeys = false;
        $hasAsymmetricKeys = false;

        foreach ($jwkset as $jwk) {
            switch ($jwk->get('kty')) {
                case 'oct':
                    $hasSymmetricKeys = true;

                    break;

                case 'OKP':
                case 'RSA':
                case 'EC':
                    $hasAsymmetricKeys = true;

                    break;
            }
        }

        if ($hasAsymmetricKeys && $hasSymmetricKeys) {
            $bag->add(Message::medium('This key set mixes symmetric and assymetric keys.'));
        }
    }
}
