<?php declare(strict_types=1);
/**
 * @author Lukáš Jelič
 */

namespace Graphette\Graphette\Scalar;


abstract class ScalarType extends \GraphQL\Type\Definition\ScalarType {

    /**
     * Returns PHP type of this Scalar type (scalar php type or class name)
     * @return string
     */
    abstract public static function getType(): string;

}
