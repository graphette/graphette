<?php declare(strict_types=1);
/**
 * @author Lukáš Jelič
 */

namespace Graphette\Graphette\TypeRegistry;


interface TypeRegistryBuilderFactory {

    public function create(): TypeRegistryBuilder;

}
