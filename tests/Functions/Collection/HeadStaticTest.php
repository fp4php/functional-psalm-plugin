<?php

declare(strict_types=1);

namespace Fp\PsalmPlugin\Test\Functions\Collection;

use Fp\Functional\Option\Option;

use function Fp\Collection\head;

final class HeadStaticTest
{
    /**
     * @param array<string, int> $coll
     * @return Option<int>
     */
    public function testWithArray(array $coll): Option
    {
        return head($coll);
    }
}
