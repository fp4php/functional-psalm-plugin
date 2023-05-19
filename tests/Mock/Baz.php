<?php

declare(strict_types=1);

namespace Fp\PsalmPlugin\Test\Mock;

final class Baz
{
    public string $name = 'no-construct';

    public function __toString(): string
    {
        return 'Baz()';
    }
}
