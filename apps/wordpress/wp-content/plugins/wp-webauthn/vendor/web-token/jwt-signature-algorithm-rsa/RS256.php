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

namespace Jose\Component\Signature\Algorithm;

final class RS256 extends RSAPKCS1
{
    public function name(): string
    {
        return 'RS256';
    }

    protected function getAlgorithm(): string
    {
        return 'sha256';
    }
}
