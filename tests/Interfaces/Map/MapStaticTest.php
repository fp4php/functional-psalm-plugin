<?php

declare(strict_types=1);

namespace Fp\PsalmPlugin\Test\Interfaces\Map;

use Fp\Collections\Map;
use Fp\Collections\Seq;
use Fp\PsalmPlugin\Test\Mock\Foo;

final class MapStaticTest
{
    /**
     * @param Map<string, int> $coll
     * @return array<string, int>
     */
    public function testToAssocArrayWithValidInput(Map $coll): array
    {
        return $coll->toArray();
    }

    /**
     * @param Seq<array{string, int}> $coll
     * @return array<string, int>
     */
    public function testToAssocArrayFromSeq(Seq $coll): array
    {
        return $coll
            ->toHashMap()
            ->toArray();
    }
}
