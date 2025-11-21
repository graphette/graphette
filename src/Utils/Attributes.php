<?php declare(strict_types=1);
/**
 * @author Lukáš Jelič
 */

namespace Graphette\Graphette\Utils;



class Attributes {

    public static function getSingleAttributeInstance(\ReflectionClass|\ReflectionProperty|\ReflectionParameter $reflection, string $attributeType, bool $throwWhenMultiple = true): mixed {
        $instances = self::getAttributeInstances($reflection, $attributeType);

        if ($throwWhenMultiple && count($instances) > 1) {
            throw new Exception\AttributesException(sprintf('Expected at most one attribute of type %s, got %d', $attributeType, count($instances)));
        }

        return self::getFirstElementOfArray($instances);
    }

    /**
     * @return array<mixed>
     */
    public static function getAttributeInstances(\ReflectionClass|\ReflectionProperty|\ReflectionParameter $reflection, string $attributeType = null): array {
        $attributes = $reflection->getAttributes();

        $instances = [];
        foreach ($attributes as $attribute) {
            $attributeInstance = $attribute->newInstance();

            if ($attributeType !== null && $attributeInstance instanceof $attributeType === false) {
                continue;
            }

            $instances[] = $attributeInstance;
        }
        return $instances;
    }

    private static function getFirstElementOfArray(array $array): mixed {
        $result = reset($array);

        if($result === false) {
            return null;
        }
        return $result;
    }

}
