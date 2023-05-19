<?php

declare(strict_types=1);

namespace Fp\PsalmPlugin\Plugin\Hook\FunctionReturnTypeProvider;

use Fp\Functional\Option\Option;
use Fp\Operations\FoldOperation;
use Fp\PsalmPlugin\Plugin\Util\GetCollectionTypeParams;
use Fp\PsalmPlugin\Toolkit\PsalmApi;
use Psalm\Plugin\EventHandler\Event\FunctionReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\FunctionReturnTypeProviderInterface;
use Psalm\Type\Atomic\TGenericObject;
use Psalm\Type\Union;

use function Fp\Callable\ctor;
use function Fp\Collection\sequenceOptionT;

final class FoldFunctionReturnTypeProvider implements FunctionReturnTypeProviderInterface
{
    public static function getFunctionIds(): array
    {
        return [strtolower('Fp\Collection\fold')];
    }

    public static function getFunctionReturnType(FunctionReturnTypeProviderEvent $event): ?Union
    {
        return Option::some($event)
            ->flatMap(fn() => sequenceOptionT(
                fn() => PsalmApi::$args->getCallArgs($event)
                    ->lastElement()
                    ->map(fn($i) => $i->type)
                    ->flatMap(GetCollectionTypeParams::value(...)),
                fn() => PsalmApi::$args->getCallArgs($event)
                    ->firstElement()
                    ->map(fn($i) => $i->type)
                    ->map(PsalmApi::$types->asNonLiteralType(...)),
            ))
            ->mapN(fn(Union $A, Union $TInit) => [
                new TGenericObject(FoldOperation::class, [$A, $TInit]),
            ])
            ->map(ctor(Union::class))
            ->get();
    }
}
