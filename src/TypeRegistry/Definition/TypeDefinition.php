<?php

namespace Graphette\Graphette\TypeRegistry\Definition;

abstract class TypeDefinition {

    private string $name;

    public function __construct(
        string $name,
    ) {
        $this->name = $name;
    }

    public function getName(): string {
        return $this->name;
    }

    /**
     * @return array<FieldDefinition>
     */
    abstract public function getFieldDefinitions(): array;

    abstract public function printTypeFactoryMethodBody(): string;


}
