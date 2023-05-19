<?php

declare(strict_types=1);

namespace Fp\PsalmPlugin\Plugin\Hook\MethodReturnTypeProvider;

use Fp\Collections\ArrayList;
use Fp\Collections\HashMap;
use Fp\Collections\HashSet;
use Fp\Collections\LinkedList;
use Fp\Collections\Map;
use Fp\Collections\NonEmptyArrayList;
use Fp\Collections\NonEmptyHashMap;
use Fp\Collections\NonEmptyHashSet;
use Fp\Collections\NonEmptyLinkedList;
use Fp\Collections\NonEmptyMap;
use Fp\Collections\NonEmptySeq;
use Fp\Collections\NonEmptySet;
use Fp\Collections\Seq;
use Fp\Collections\Set;
use Fp\PsalmPlugin\Plugin\Util\TypeRefinement\PredicateExtractor;
use Fp\PsalmPlugin\Plugin\Util\TypeRefinement\RefineForEnum;
use Fp\Streams\Stream;
use Fp\PsalmPlugin\Plugin\Util\TypeRefinement\CollectionTypeParams;
use Fp\PsalmPlugin\Plugin\Util\TypeRefinement\RefineByPredicate;
use Fp\PsalmPlugin\Plugin\Util\TypeRefinement\RefinementContext;
use Psalm\Plugin\EventHandler\Event\MethodReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\MethodReturnTypeProviderInterface;
use Psalm\Type;
use Psalm\Internal\Analyzer\StatementsAnalyzer;
use Fp\Functional\Option\Option;
use Psalm\Type\Atomic\TGenericObject;
use Psalm\Type\Union;

use function Fp\Callable\ctor;
use function Fp\Collection\sequenceOptionT;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveTrue;

final class CollectionFilterMethodReturnTypeProvider implements MethodReturnTypeProviderInterface
{
    public static function getMethodNames(): array
    {
        return [
            'filter',
            strtolower('filterKV'),
            strtolower('filterNotNull'),
        ];
    }

    public static function getClassLikeNames(): array
    {
        return [
            HashMap::class,
            NonEmptyHashMap::class,
            LinkedList::class,
            NonEmptyLinkedList::class,
            ArrayList::class,
            NonEmptyArrayList::class,
            HashSet::class,
            NonEmptyHashSet::class,
            Seq::class,
            NonEmptySeq::class,
            Set::class,
            NonEmptySet::class,
            Map::class,
            NonEmptyMap::class,
            Stream::class,
        ];
    }

    public static function getMethodReturnType(MethodReturnTypeProviderEvent $event): ?Union
    {
        return proveTrue(in_array($event->getMethodNameLowercase(), self::getMethodNames()))
            ->flatMap(fn() => sequenceOptionT(
                fn() => Option::some($event->getMethodNameLowercase() === strtolower('filterKV')
                    ? RefineForEnum::KeyValue
                    : RefineForEnum::Value),
                fn() => PredicateExtractor::extract($event),
                fn() => Option::some($event->getContext()),
                fn() => proveOf($event->getSource(), StatementsAnalyzer::class),
                fn() => Option::fromNullable($event->getTemplateTypeParameters())
                    ->map(fn($template_params) => 2 === count($template_params)
                        ? new CollectionTypeParams($template_params[0], $template_params[1])
                        : new CollectionTypeParams(Type::getArrayKey(), $template_params[0])),
            ))
            ->mapN(ctor(RefinementContext::class))
            ->map(RefineByPredicate::for(...))
            ->map(fn(CollectionTypeParams $result) => self::getReturnType($event, $result))
            ->get();
    }

    private static function getReturnType(MethodReturnTypeProviderEvent $event, CollectionTypeParams $result): Union
    {
        $class_name = str_replace('NonEmpty', '', $event->getFqClasslikeName());

        $template_params = 2 === count($event->getTemplateTypeParameters() ?? [])
            ? [$result->key_type, $result->val_type]
            : [$result->val_type];

        return new Union([
            new TGenericObject($class_name, $template_params),
        ]);
    }
}
