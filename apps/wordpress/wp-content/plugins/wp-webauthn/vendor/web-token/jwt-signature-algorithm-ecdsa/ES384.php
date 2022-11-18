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

final class ES384 extends ECDSA
{
    public function name(): string
    {
        return 'ES384';
    }

    protected function getHashAlgorithm(): string
    {
        return 'sha384';
    }

    protected function getSignaturePartLength(): int
    {
        return 96;
    }
}
