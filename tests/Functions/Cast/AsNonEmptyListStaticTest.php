<?php

declare(strict_types=1);

namespace Fp\PsalmPlugin\Test\Functions\Cast;

use Fp\Functional\Option\Option;

use function Fp\Cast\asNonEmptyList;

final class AsNonEmptyListStaticTest
{
    /**
     * @param iterable<string, int> $coll
     * @return Option<non-empty-list<int>>
     */
    public function testWithIterable(iterable $coll): Option
    {
        return asNonEmptyList($coll);
    }
}
