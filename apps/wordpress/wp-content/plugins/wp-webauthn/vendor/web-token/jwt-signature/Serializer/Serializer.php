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

namespace Jose\Component\Signature\Serializer;

use function array_key_exists;

abstract class Serializer implements JWSSerializer
{
    protected function isPayloadEncoded(array $protectedHeader): bool
    {
        return !array_key_exists('b64', $protectedHeader) || true === $protectedHeader['b64'];
    }
}
