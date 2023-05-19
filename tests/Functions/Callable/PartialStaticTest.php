<?php

declare(strict_types=1);

namespace Fp\PsalmPlugin\Test\Functions\Callable;

use Closure;

use function Fp\Callable\partialLeft;
use function Fp\Callable\partialRight;

final class PartialStaticTest
{
    /**
     * @psalm-return (pure-Closure(string, bool): true)
     */
    public function testPartialLeftForClosure3(): Closure
    {
        return partialLeft(fn(int $_a, string $_b, bool $_c): bool => true, 1);
    }

    /**
     * @psalm-return (pure-Closure(string): true)
     */
    public function testPartialLeftForClosure2(): Closure
    {
        return partialLeft(fn(int $_a, string $_b): bool => true, 1);
    }

    /**
     * @psalm-return (pure-Closure(): true)
     */
    public function testPartialLeftForClosure1(): Closure
    {
        return partialLeft(fn(int $_a): bool => true, 1);
    }

    /**
     * @psalm-return (pure-Closure(int, string): true)
     */
    public function testPartialRightForClosure3(): Closure
    {
        return partialRight(fn(int $_a, string $_b, bool $_c): bool => true, true);
    }

    /**
     * @psalm-return (pure-Closure(int): true)
     */
    public function testPartialRightForClosure2(): Closure
    {
        return partialRight(fn(int $_a, string $_b) => true, "");
    }

    /**
     * @psalm-return (pure-Closure(): true)
     */
    public function testPartialRightForClosure1(): Closure
    {
        return partialRight(fn(int $_): bool => true, 1);
    }
}
