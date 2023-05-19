<?php

declare(strict_types=1);

namespace Fp\PsalmPlugin\Test\Functions\Collection;

use function Fp\Collection\filter;
use function Fp\Collection\filterKV;

/**
 * @psalm-type Shape = array{name: string, postcode: int}
 * @psalm-type ShapeWithPossiblyUndefinedPostcode = array{name?: string, postcode?: int|string}
 */
final class FilterStaticTest
{
    /**
     * @param list<int> $list
     * @return list<int>
     */
    public function testPreserveListType(array $list): array
    {
        return filter($list, fn($i) => $i > 42);
    }

    /**
     * @param non-empty-list<int> $list
     * @return list<int>
     */
    public function testPreserveListTypeFromNonEmptyList(array $list): array
    {
        return filter($list, fn($i) => $i > 42);
    }

    /**
     * @param array<string, null|int> $coll
     * @return array<string, int>
     */
    public function testRefineNotNull(array $coll): array
    {
        return filter(
            $coll,
            fn(null|int $v) => null !== $v
        );
    }

    /**
     * @param array<string, ShapeWithPossiblyUndefinedPostcode> $coll
     * @return array<string, array{name: string, postcode: int}>
     */
    public function testRefineShapeType(array $coll): array
    {
        return filter(
            $coll,
            fn(array $v) =>
                array_key_exists('name', $v) &&
                array_key_exists('postcode', $v) &&
                is_int($v['postcode'])
        );
    }

    /**
     * @param array<string, array>  $coll
     * @return array<string, array{name: string, postcode: int}>
     */
    public function testRefineShapeWithPsalmAssert(array $coll): array
    {
        return filter(
            $coll,
            fn(array $v) => $this->isValidShape($v)
        );
    }

    /**
     * @psalm-assert-if-true Shape $shape
     */
    public function isValidShape(array $shape): bool
    {
        return array_key_exists('name', $shape) &&
            array_key_exists('postcode', $shape) &&
            is_int($shape['postcode']);
    }

    /**
     * @psalm-assert-if-true Shape $shape
     */
    public function isValidShapeStatic(array $shape): bool
    {
        return array_key_exists('name', $shape) &&
            array_key_exists('postcode', $shape) &&
            is_int($shape['postcode']);
    }

    /**
     * @param array<string, array>  $coll
     * @return array<string, array{name: string, postcode: int}>
     */
    public function testWithFirstClassCallableMethod(array $coll): array
    {
        return filter($coll, $this->isValidShape(...));
    }

    /**
     * @param array<string, array>  $coll
     * @return array<string, array{name: string, postcode: int}>
     */
    public function testWithFirstClassCallableStaticMethod(array $coll): array
    {
        return filter($coll, self::isValidShape(...));
    }

    /**
     * @param array<string, int|string>  $coll
     * @return array<string, int>
     */
    public function testWithFirstClassCallableFunction(array $coll): array
    {
        return filter($coll, is_int(...));
    }

    /**
     * @psalm-assert-if-true int $key
     * @psalm-assert-if-true string $val
     */
    public function assertKV(mixed $key, mixed $val): bool
    {
        return is_int($key) && is_string($val);
    }

    /**
     * @param array<array-key, mixed> $coll
     * @return array<int, string>
     */
    public function testFilterKVWithFirstClassCallable(array $coll): array
    {
        return filterKV($coll, $this->assertKV(...));
    }
}
