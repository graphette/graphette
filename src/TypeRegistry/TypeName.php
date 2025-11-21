<?php declare(strict_types=1);
/**
 * @author Lukáš Jelič
 */

namespace Graphette\Graphette\TypeRegistry;


class TypeName {

    private string $rootNamespace;

    public function __construct(string $rootNamespace) {
        $this->rootNamespace = self::normalizeRootNamespace($rootNamespace);
    }

    public function generate(\ReflectionClass $reflectionClass): string {
        $className = $reflectionClass->getName();
        $className = str_replace($this->rootNamespace, '', $className);

		$classNameExploded = explode('\\', $className);
		if (self::compareLastToArrayElements($classNameExploded)) {
			array_pop($classNameExploded);
		}

		return implode('', $classNameExploded);
    }

    private static function normalizeRootNamespace(string $rootNamespace): string {
        $rootNamespace = rtrim($rootNamespace, '\\') . '\\';
        return ltrim($rootNamespace, '\\');
    }

	private static function compareLastToArrayElements($arr): bool {
		$count = count($arr);
		if ($count < 2) {
			return false; // There are not enough elements to compare
		}

		return $arr[$count - 1] === $arr[$count - 2];
	}

}
