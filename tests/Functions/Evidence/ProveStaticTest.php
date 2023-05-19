<?php

declare(strict_types=1);

namespace Fp\PsalmPlugin\Test\Functions\Evidence;

use Fp\Functional\Option\Option;
use Fp\PsalmPlugin\Test\Mock\Bar;
use Fp\PsalmPlugin\Test\Mock\Foo;

use function Fp\Evidence\proveOf;

final class ProveStaticTest
{
    /**
     * @return Option<Foo>
     */
    public function testProveOf(): Option
    {
        return proveOf(new Foo(1), Foo::class);
    }

    /**
     * @return Option<Foo|Bar>
     */
    public function testProveOfWithMultipleClasses(): Option
    {
        return proveOf(new Foo(1), [Foo::class, Bar::class]);
    }
}
