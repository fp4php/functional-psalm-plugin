<?php

declare(strict_types=1);

namespace Fp\PsalmPlugin\Toolkit;

use Psalm\Type\Atomic;
use Fp\Functional\Option\Option;
use Psalm\Storage\ClassLikeStorage;
use function array_key_exists;
use function Fp\Evidence\proveOf;
use function strtolower;

final class Classlikes
{
    private function toFqClassName(string|Atomic\TNamedObject|ClassLikeStorage $classlike): string
    {
        return match (true) {
            $classlike instanceof ClassLikeStorage => $classlike->name,
            $classlike instanceof Atomic\TNamedObject => $classlike->value,
            default => $classlike,
        };
    }

    /**
     * @return Option<ClassLikeStorage>
     */
    public function getStorage(string|Atomic\TNamedObject $classlike): Option
    {
        return Option::fromNullable(
            PsalmApi::$codebase->classlikes->getStorageFor($this->toFqClassName($classlike))
        );
    }

    public function classExtends(string|Atomic\TNamedObject|ClassLikeStorage $classlike, string $possible_parent): bool
    {
        return PsalmApi::$codebase->classlikes->classExtends($this->toFqClassName($classlike), $possible_parent);
    }

    public function classImplements(string|Atomic\TNamedObject|ClassLikeStorage $classlike, string $interface): bool
    {
        return PsalmApi::$codebase->classlikes->classImplements($this->toFqClassName($classlike), $interface);
    }

    public function isTraitUsed(string|Atomic\TNamedObject|ClassLikeStorage $classlike, string $trait): bool
    {
        $storage = $classlike instanceof ClassLikeStorage
            ? Option::some($classlike)
            : $this->getStorage($classlike);

        return $storage
            ->map(fn(ClassLikeStorage $s) => array_key_exists(strtolower($trait), $s->used_traits))
            ->getOrElse(false);
    }

    public function toShortName(ClassLikeStorage|Atomic\TNamedObject|string $fqn_classlike_name): string
    {
        $name = match (true) {
            $fqn_classlike_name instanceof ClassLikeStorage => $fqn_classlike_name->name,
            $fqn_classlike_name instanceof Atomic\TNamedObject => $fqn_classlike_name->value,
            default => $fqn_classlike_name,
        };

        return str_contains($name, '\\') ? substr(strrchr($name, '\\'), 1) : $name;
    }
}
