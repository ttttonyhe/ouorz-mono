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

class KeyAnalyzerManager
{
    /**
     * @var KeyAnalyzer[]
     */
    private $analyzers = [];

    /**
     * Adds a Key Analyzer to the manager.
     */
    public function add(KeyAnalyzer $analyzer): void
    {
        $this->analyzers[] = $analyzer;
    }

    /**
     * This method will analyze the JWK object using all analyzers.
     * It returns a message bag that may contains messages.
     */
    public function analyze(JWK $jwk): MessageBag
    {
        $bag = new MessageBag();
        foreach ($this->analyzers as $analyzer) {
            $analyzer->analyze($jwk, $bag);
        }

        return $bag;
    }
}
