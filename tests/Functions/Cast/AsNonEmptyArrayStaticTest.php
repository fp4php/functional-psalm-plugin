<?php

declare(strict_types=1);

namespace Fp\PsalmPlugin\Test\Functions\Cast;

use Fp\Functional\Option\Option;

use function Fp\Cast\asNonEmptyArray;

final class AsNonEmptyArrayStaticTest
{
    /**
     * @param iterable<string, int> $coll
     * @return Option<non-empty-array<string, int>>
     */
    public function testWithIterable(iterable $coll): Option
    {
        return asNonEmptyArray($coll);
    }
}
