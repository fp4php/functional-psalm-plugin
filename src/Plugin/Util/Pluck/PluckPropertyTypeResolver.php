<?php

declare(strict_types=1);

namespace Fp\PsalmPlugin\Plugin\Util\Pluck;

use Fp\Functional\Option\Option;
use Fp\PsalmPlugin\Toolkit\PsalmApi;
use Psalm\Issue\PossiblyUndefinedArrayOffset;
use Psalm\Issue\UndefinedPropertyFetch;
use Psalm\IssueBuffer;
use Psalm\Storage\ClassLikeStorage;
use Psalm\Storage\PropertyStorage;
use Psalm\Type;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Union;
use function Fp\Collection\at;
use function Fp\Evidence\proveOf;

final class PluckPropertyTypeResolver
{
    /**
     * @return Option<Union>
     */
    public static function resolve(PluckResolveContext $context): Option
    {
        return self::getTypesForObject($context)
            ->orElse(fn() => self::getTypesForObjectLikeArray($context));
    }

    /**
     * @return Option<Union>
     * @todo: Templated properties are not supported
     */
    private static function getTypesForObject(PluckResolveContext $context): Option
    {
        return proveOf($context->object, TNamedObject::class)
            ->flatMap(PsalmApi::$classlikes->getStorage(...))
            ->flatMap(fn(ClassLikeStorage $storage) => at($storage->properties, $context->key->value)
                ->orElse(fn() => self::getPropertyFromParentClass($storage, $context->key->value))
                ->map(fn(PropertyStorage $property) => $property->type ?? Type::getMixed())
                ->orElse(fn() => self::undefinedPropertyIssue($storage->name, $context))
            );
    }

    /**
     * @return Option<PropertyStorage>
     */
    private static function getPropertyFromParentClass(ClassLikeStorage $storage, string $property): Option
    {
        return at($storage->declaring_property_ids, $property)
            ->flatMap(PsalmApi::$classlikes->getStorage(...))
            ->flatMap(fn(ClassLikeStorage $parent) => at($parent->properties, $property));
    }

    /**
     * @return Option<never>
     */
    private static function undefinedPropertyIssue(string $class, PluckResolveContext $context): Option
    {
        $issue = new UndefinedPropertyFetch(
            message: "Property '{$context->key->value}' is undefined",
            code_location: $context->location,
            property_id: "{$class}::\${$context->key->value}",
        );

        IssueBuffer::accepts($issue, $context->source->getSuppressedIssues());
        return Option::none();
    }

    /**
     * @return Option<Union>
     */
    private static function getTypesForObjectLikeArray(PluckResolveContext $context): Option
    {
        return proveOf($context->object, TKeyedArray::class)
            ->flatMap(fn(TKeyedArray $array) => at($array->properties, $context->key->value)
                ->orElse(fn() => self::undefinedArrayKeyIssue($context)));
    }

    /**
     * @return Option<never>
     */
    private static function undefinedArrayKeyIssue(PluckResolveContext $context): Option
    {
        $issue = new PossiblyUndefinedArrayOffset(
            message: "Array key '{$context->key->value}' is undefined",
            code_location: $context->location,
        );

        IssueBuffer::accepts($issue, $context->source->getSuppressedIssues());
        return Option::none();
    }
}
