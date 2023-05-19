<?php

declare(strict_types=1);

namespace Fp\PsalmPlugin\Test\Functions\Cast;

use function Fp\Cast\asArray;

final class AsArrayStaticTest
{
    /**
     * @param iterable<string, int> $coll
     * @return array<string, int>
     */
    public function testWithIterable(iterable $coll): array
    {
        return asArray($coll);
    }
}
