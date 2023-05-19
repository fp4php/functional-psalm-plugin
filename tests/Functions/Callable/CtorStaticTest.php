<?php

declare(strict_types=1);

namespace Fp\PsalmPlugin\Test\Functions\Callable;

use Closure;
use Fp\PsalmPlugin\Test\Mock\Baz;
use Fp\PsalmPlugin\Test\Mock\Foo;

use function Fp\Callable\ctor;

final class CtorStaticTest
{
    /**
     * @return Closure(int $a, bool $b, bool $c): Foo
     */
    public function ctorReturnsConstructorAsClosure(): Closure
    {
        return ctor(Foo::class);
    }

    public function passOnlyOneRequiredArg(): Foo
    {
        return ctor(Foo::class)(1);
    }

    public function passOneRequiredAndOneOptionalArg(): Foo
    {
        return ctor(Foo::class)(1, false);
    }

    public function passAllArgs(): Foo
    {
        return ctor(Foo::class)(1, false, true);
    }

    public function passInvalidArg(): Foo
    {
        /** @psalm-suppress InvalidArgument */
        return ctor(Foo::class)(1, 'false', true);
    }

    public function passExtraArg(): Foo
    {
        /** @psalm-suppress TooManyArguments */
        return ctor(Foo::class)(1, false, true, 'extra');
    }

    public function passArgWhenThereIsNoConstructor(): Baz
    {
        /** @psalm-suppress TooManyArguments */
        return ctor(Baz::class)('extra');
    }
}
