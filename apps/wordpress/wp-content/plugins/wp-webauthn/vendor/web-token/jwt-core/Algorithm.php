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

namespace Jose\Component\Core;

interface Algorithm
{
    /**
     * Returns the name of the algorithm.
     */
    public function name(): string;

    /**
     * Returns the key types suitable for this algorithm (e.g. "oct", "RSA"...).
     *
     * @return string[]
     */
    public function allowedKeyTypes(): array;
}
