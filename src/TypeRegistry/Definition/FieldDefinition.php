<?php

namespace Graphette\Graphette\TypeRegistry\Definition;

use GraphQL\Type\Definition\ResolveInfo;
use Nette\PhpGenerator\Literal;
use Graphette\Graphette\Attribute\Field;
use Graphette\Graphette\Attribute\ListOf;
use Graphette\Graphette\Attribute\Unions;
use Graphette\Graphette\Utils\Attributes;

abstract class FieldDefinition {

    abstract public function getName(): string;

    abstract public function extractFieldDefinition(): array;

}
