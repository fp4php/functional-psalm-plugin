<?php

declare(strict_types=1);

namespace Fp\PsalmPlugin\Plugin\Hook\MethodReturnTypeProvider;

use Fp\Functional\Either\Either;
use Fp\Functional\Option\Option;
use Fp\PsalmPlugin\Plugin\Util\TypeRefinement\CollectionTypeParams;
use Fp\PsalmPlugin\Plugin\Util\TypeRefinement\PredicateExtractor;
use Fp\PsalmPlugin\Plugin\Util\TypeRefinement\RefineByPredicate;
use Fp\PsalmPlugin\Plugin\Util\TypeRefinement\RefineForEnum;
use Fp\PsalmPlugin\Plugin\Util\TypeRefinement\RefinementContext;
use Fp\PsalmPlugin\Toolkit\PsalmApi;
use PhpParser\Node\Arg;
use Psalm\Internal\Analyzer\StatementsAnalyzer;
use Psalm\Plugin\EventHandler\Event\MethodReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\MethodReturnTypeProviderInterface;
use Psalm\Type;
use Psalm\Type\Atomic\TGenericObject;
use Psalm\Type\Atomic\TClosure;
use Psalm\Type\Atomic\TCallable;
use Psalm\Type\Union;

use function Fp\Callable\ctor;
use function Fp\Collection\first;
use function Fp\Collection\second;
use function Fp\Collection\sequenceOptionT;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveTrue;
use function Fp\Evidence\of;

final class EitherFilterOrElseMethodReturnTypeProvider implements MethodReturnTypeProviderInterface
{
    public static function getClassLikeNames(): array
    {
        return [Either::class];
    }

    public static function getMethodReturnType(MethodReturnTypeProviderEvent $event): ?Union
    {
        return proveTrue(strtolower('filterOrElse') === $event->getMethodNameLowercase())
            ->flatMap(fn() => sequenceOptionT(
                fn() => Option::some(RefineForEnum::Value),
                fn() => PredicateExtractor::extract($event),
                fn() => Option::some($event->getContext()),
                fn() => proveOf($event->getSource(), StatementsAnalyzer::class),
                fn() => second($event->getTemplateTypeParameters() ?? [])
                    ->map(fn($value_type) => [Type::getArrayKey(), $value_type])
                    ->mapN(ctor(CollectionTypeParams::class)),
            ))
            ->mapN(ctor(RefinementContext::class))
            ->map(RefineByPredicate::for(...))
            ->map(fn(CollectionTypeParams $result) => new TGenericObject(Either::class, [
                self::getOutLeft($event),
                $result->val_type,
            ]))
            ->map(fn(TGenericObject $result) => new Union([$result]))
            ->get();
    }

    private static function getOutLeft(MethodReturnTypeProviderEvent $event): Union
    {
        return Type::combineUnionTypes(
            first($event->getTemplateTypeParameters() ?? [])
                ->getOrCall(fn() => Type::getNever()),
            second($event->getCallArgs())
                ->flatMap(fn(Arg $arg) => PsalmApi::$args->getArgType($event, $arg))
                ->flatMap(PsalmApi::$types->asSingleAtomic(...))
                ->flatMap(of([TClosure::class, TCallable::class]))
                ->flatMap(fn(TClosure|TCallable $func) => Option::fromNullable($func->return_type))
                ->getOrCall(fn() => Type::getNever()),
        );
    }
}
