<?php

declare(strict_types=1);

namespace Fp\PsalmPlugin\Test\Functions\Collection;

use Fp\Functional\Option\Option;

use function Fp\Collection\second;

final class SecondStaticTest
{
    /**
     * @param array<string, int> $coll
     * @return Option<int>
     */
    public function testWithArray(array $coll): Option
    {
        return second($coll);
    }
}
