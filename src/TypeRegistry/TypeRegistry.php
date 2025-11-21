<?php declare(strict_types=1);
/**
 * @author Lukáš Jelič
 */

namespace Graphette\Graphette\TypeRegistry;


use GraphQL\Type\Definition\Type;

interface TypeRegistry {

    public function get(string $name): Type;

    public function getInvisibleTypes(): array;

    public function has(string $name): bool;

    public function getClassNameByType(string $type): ?string;

    public function getAutoInstantiateClass(string $type): ?string;

    public function getFieldInfo(string $type, string $field): FieldInfo;

    public function resolveObjectType(object $object): ?string;

}
