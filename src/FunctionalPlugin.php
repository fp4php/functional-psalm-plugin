<?php

declare(strict_types=1);

namespace Fp\PsalmPlugin;

use Fp\PsalmPlugin\Plugin\Hook\AfterExpressionAnalysis\ProveTrueExpressionAnalyzer;
use Fp\PsalmPlugin\Plugin\Hook\DynamicFunctionStorageProvider\PipeFunctionStorageProvider;
use Fp\PsalmPlugin\Plugin\Hook\FunctionReturnTypeProvider\CtorFunctionReturnTypeProvider;
use Fp\PsalmPlugin\Plugin\Hook\FunctionReturnTypeProvider\FilterFunctionReturnTypeProvider;
use Fp\PsalmPlugin\Plugin\Hook\FunctionReturnTypeProvider\FilterNotNullFunctionReturnTypeProvider;
use Fp\PsalmPlugin\Plugin\Hook\FunctionReturnTypeProvider\FoldFunctionReturnTypeProvider;
use Fp\PsalmPlugin\Plugin\Hook\FunctionReturnTypeProvider\PartialFunctionReturnTypeProvider;
use Fp\PsalmPlugin\Plugin\Hook\FunctionReturnTypeProvider\PartitionFunctionReturnTypeProvider;
use Fp\PsalmPlugin\Plugin\Hook\FunctionReturnTypeProvider\PluckFunctionReturnTypeProvider;
use Fp\PsalmPlugin\Plugin\Hook\FunctionReturnTypeProvider\SequenceEitherFunctionReturnTypeProvider;
use Fp\PsalmPlugin\Plugin\Hook\FunctionReturnTypeProvider\SequenceOptionFunctionReturnTypeProvider;
use Fp\PsalmPlugin\Plugin\Hook\MethodReturnTypeProvider\CollectionFilterMethodReturnTypeProvider;
use Fp\PsalmPlugin\Plugin\Hook\MethodReturnTypeProvider\EitherFilterOrElseMethodReturnTypeProvider;
use Fp\PsalmPlugin\Plugin\Hook\MethodReturnTypeProvider\EitherGetReturnTypeProvider;
use Fp\PsalmPlugin\Plugin\Hook\MethodReturnTypeProvider\FoldMethodReturnTypeProvider;
use Fp\PsalmPlugin\Plugin\Hook\MethodReturnTypeProvider\MapTapNMethodReturnTypeProvider;
use Fp\PsalmPlugin\Plugin\Hook\MethodReturnTypeProvider\OptionFilterMethodReturnTypeProvider;
use Fp\PsalmPlugin\Plugin\Hook\MethodReturnTypeProvider\OptionGetReturnTypeProvider;
use Fp\PsalmPlugin\Plugin\Hook\MethodReturnTypeProvider\PluckMethodReturnTypeProvider;
use Fp\PsalmPlugin\Plugin\Hook\MethodReturnTypeProvider\SeparatedToEitherMethodReturnTypeProvider;
use Fp\PsalmPlugin\Toolkit\PsalmApi;
use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\RegistrationInterface;
use SimpleXMLElement;

/**
 * Plugin entrypoint
 */
final class FunctionalPlugin implements PluginEntryPointInterface
{
    public function __invoke(RegistrationInterface $registration, ?SimpleXMLElement $config = null): void
    {
        PsalmApi::init();

        $register = function(string $hook) use ($registration): void {
            if (class_exists($hook)) {
                $registration->registerHooksFromClass($hook);
            }
        };

        $register(ProveTrueExpressionAnalyzer::class);

        $register(OptionGetReturnTypeProvider::class);
        $register(EitherGetReturnTypeProvider::class);
        $register(EitherFilterOrElseMethodReturnTypeProvider::class);

        $register(PartialFunctionReturnTypeProvider::class);
        $register(PartitionFunctionReturnTypeProvider::class);
        $register(PluckFunctionReturnTypeProvider::class);

        $register(FilterFunctionReturnTypeProvider::class);
        $register(CollectionFilterMethodReturnTypeProvider::class);
        $register(OptionFilterMethodReturnTypeProvider::class);

        $register(SequenceOptionFunctionReturnTypeProvider::class);
        $register(SequenceEitherFunctionReturnTypeProvider::class);

        $register(FilterNotNullFunctionReturnTypeProvider::class);
        $register(FoldFunctionReturnTypeProvider::class);
        $register(FoldMethodReturnTypeProvider::class);
        $register(MapTapNMethodReturnTypeProvider::class);
        $register(PluckMethodReturnTypeProvider::class);
        $register(CtorFunctionReturnTypeProvider::class);
        $register(SeparatedToEitherMethodReturnTypeProvider::class);

        $register(PipeFunctionStorageProvider::class);
    }
}
