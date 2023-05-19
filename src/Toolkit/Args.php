<?php

declare(strict_types=1);

namespace Fp\PsalmPlugin\Toolkit;

use Fp\Collections\ArrayList;
use PhpParser\Node;
use Fp\Functional\Option\Option;
use Psalm\CodeLocation;
use Psalm\NodeTypeProvider;
use Psalm\Plugin\EventHandler\Event\AfterExpressionAnalysisEvent;
use Psalm\Plugin\EventHandler\Event\AfterFunctionCallAnalysisEvent;
use Psalm\Plugin\EventHandler\Event\AfterStatementAnalysisEvent;
use Psalm\Plugin\EventHandler\Event\AfterMethodCallAnalysisEvent;
use Psalm\Plugin\EventHandler\Event\FunctionReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\Event\MethodReturnTypeProviderEvent;
use Psalm\StatementsSource;
use Psalm\Type;
use Psalm\Type\Union;

use function Fp\Evidence\proveOf;

final class Args
{
    /**
     * @return Option<Union>
     */
    public function getArgType(
        StatementsSource |
        NodeTypeProvider |
        AfterStatementAnalysisEvent |
        AfterMethodCallAnalysisEvent |
        AfterFunctionCallAnalysisEvent |
        MethodReturnTypeProviderEvent |
        FunctionReturnTypeProviderEvent |
        AfterExpressionAnalysisEvent $from,
        Node\Arg $for,
    ): Option {
        return PsalmApi::$types->getType($from, $for->value);
    }

    /**
     * @return ArrayList<CallArg>
     */
    public function getCallArgs(
        MethodReturnTypeProviderEvent |
        FunctionReturnTypeProviderEvent |
        AfterFunctionCallAnalysisEvent |
        AfterMethodCallAnalysisEvent $from,
    ): ArrayList {
        $source = match (true) {
            $from instanceof MethodReturnTypeProviderEvent => $from->getSource(),
            $from instanceof FunctionReturnTypeProviderEvent => $from->getStatementsSource(),
            $from instanceof AfterFunctionCallAnalysisEvent => $from->getStatementsSource(),
            $from instanceof AfterMethodCallAnalysisEvent => $from->getStatementsSource(),
        };

        $args = match (true) {
            $from instanceof AfterFunctionCallAnalysisEvent => $from->getExpr()->getArgs(),
            $from instanceof AfterMethodCallAnalysisEvent => $from->getExpr()->getArgs(),
            default => $from->getCallArgs(),
        };

        return ArrayList
            ::collect($args)
            ->map(fn($arg) => new CallArg(
                node: $arg,
                location: new CodeLocation($source, $arg),
                type: $this->getArgType($from, $arg)->getOrCall(fn() => Type::getMixed()),
            ));
    }

    /**
     * @return Option<CallArg>
     */
    public function getFirstCallArg(
        MethodReturnTypeProviderEvent |
        FunctionReturnTypeProviderEvent |
        AfterFunctionCallAnalysisEvent |
        AfterMethodCallAnalysisEvent $from,
    ): Option {
        return $this->getCallArgs($from)->head();
    }

    /**
     * @return Option<Union>
     */
    public function getFirstCallArgType(
        MethodReturnTypeProviderEvent |
        FunctionReturnTypeProviderEvent |
        AfterFunctionCallAnalysisEvent |
        AfterMethodCallAnalysisEvent $from,
    ): Option {
        return $this->getFirstCallArg($from)->map(fn(CallArg $arg) => $arg->type);
    }
}
