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

use Base64Url\Base64Url;
use Brick\Math\BigInteger;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\Ecc\NistCurve;
use RuntimeException;

final class ES512KeyAnalyzer implements KeyAnalyzer
{
    /**
     * @throws RuntimeException if the component "web-token/jwt-util-ecc" is missing
     */
    public function __construct()
    {
        if (!class_exists(NistCurve::class)) {
            throw new RuntimeException('Please install web-token/jwt-util-ecc to use this key analyzer');
        }
    }

    public function analyze(JWK $jwk, MessageBag $bag): void
    {
        if ('EC' !== $jwk->get('kty')) {
            return;
        }
        if (!$jwk->has('crv')) {
            $bag->add(Message::high('Invalid key. The components "crv" is missing.'));

            return;
        }
        if ('P-521' !== $jwk->get('crv')) {
            return;
        }
        $x = Base64Url::decode($jwk->get('x'));
        $xLength = 8 * mb_strlen($x, '8bit');
        $y = Base64Url::decode($jwk->get('y'));
        $yLength = 8 * mb_strlen($y, '8bit');
        if ($yLength !== $xLength || 528 !== $yLength) {
            $bag->add(Message::high('Invalid key. The components "x" and "y" size shall be 528 bits.'));
        }
        $xBI = BigInteger::fromBase(bin2hex($x), 16);
        $yBI = BigInteger::fromBase(bin2hex($y), 16);
        $curve = NistCurve::curve521();
        if (!$curve->contains($xBI, $yBI)) {
            $bag->add(Message::high('Invalid key. The point is not on the curve.'));
        }
    }
}
