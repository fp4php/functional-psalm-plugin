<?php

declare(strict_types=1);

namespace Fp\PsalmPlugin\Plugin\Hook\MethodReturnTypeProvider;

use Fp\Functional\Option\Option;
use Fp\PsalmPlugin\Plugin\Util\Pluck\PluckPropertyTypeResolver;
use Fp\PsalmPlugin\Plugin\Util\Pluck\PluckResolveContext;
use Fp\PsalmPlugin\Toolkit\PsalmApi;
use Psalm\Plugin\EventHandler\Event\MethodReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\MethodReturnTypeProviderInterface;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TGenericObject;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TLiteralString;
use Psalm\Type\Union;

use function Fp\Callable\ctor;
use function Fp\Collection\last;
use function Fp\Collection\sequenceOptionT;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveTrue;
use function Fp\Evidence\of;

final class PluckMethodReturnTypeProvider implements MethodReturnTypeProviderInterface
{
    public static function getClassLikeNames(): array
    {
        return [Option::class];
    }

    public static function getMethodReturnType(MethodReturnTypeProviderEvent $event): ?Union
    {
        return proveTrue('pluck' === $event->getMethodNameLowercase())
            ->flatMap(fn() => sequenceOptionT(
                fn() => PsalmApi::$args->getCallArgs($event)
                    ->lastElement()
                    ->map(fn($i) => $i->type)
                    ->flatMap(PsalmApi::$types->asSingleAtomic(...))
                    ->flatMap(of(TLiteralString::class)),
                fn() => Option::fromNullable($event->getTemplateTypeParameters())
                    ->flatMap(fn(array $templates) => last($templates))
                    ->flatMap(PsalmApi::$types->asSingleAtomic(...))
                    ->flatMap(fn(Atomic $atomic) => proveOf($atomic, [TNamedObject::class, TKeyedArray::class])),
                fn() => Option::some($event->getSource()),
                fn() => Option::some($event->getCodeLocation()),
            ))
            ->mapN(ctor(PluckResolveContext::class))
            ->flatMap(PluckPropertyTypeResolver::resolve(...))
            ->map(fn(Union $result) => [
                new TGenericObject(Option::class, [$result]),
            ])
            ->map(ctor(Union::class))
            ->get();
    }
}
