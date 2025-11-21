<?php

namespace Graphette\Graphette\TypeRegistry\Definition;

use GraphQL\Type\Definition\ResolveInfo;
use Nette\PhpGenerator\Literal;
use Nette\Utils\Strings;
use Graphette\Graphette\Attribute\Field;
use Graphette\Graphette\Attribute\ListOf;
use Graphette\Graphette\Attribute\NotRequired;
use Graphette\Graphette\Attribute\ScalarAlias;
use Graphette\Graphette\Attribute\Unions;
use Graphette\Graphette\TypeRegistry\TypeDefinitionProvider;
use Graphette\Graphette\Utils\Attributes;

class PropertyFieldDefinition extends FieldDefinition {

    private const RESOLVER_METHOD_PREFIX = 'resolve';

    private \ReflectionClass $typeReflectionClass;

    private \ReflectionProperty $propertyReflection;

    private Field $fieldAttribute;

    private \Closure $nameGenerator;

    private ?\ReflectionClass $resolverReflection;

    private ?\ReflectionMethod $resolverMethodReflection;

    private array $fieldArgs;

    private ?array $resolverMethodDefinition;

    public function __construct(
        \ReflectionClass $typeReflectionClass,
        \ReflectionProperty $property,
        Field $fieldAttribute,
        \Closure $nameGenerator,
        \ReflectionClass|null $typeResolver = null,
    ) {
        $this->typeReflectionClass = $typeReflectionClass;
        $this->propertyReflection = $property;
        $this->fieldAttribute = $fieldAttribute;
        $this->nameGenerator = $nameGenerator;
        $this->resolverReflection = $typeResolver;
    }

    // HIGH-LEVEL
    public function extractFieldDefinition(): array {
        $property = $this->propertyReflection;

        $definition = [
            'type'        => $this->extractFieldType($property),
            'description' => $this->getDescription(),
            'args'        => $this->getFieldArgs(),
        ];

        if ($property->hasDefaultValue()) {
            $definition['defaultValue'] = $this->parseDefaultValue($property);
        }
        return $definition;
    }

    // GETTERS AND SETTERS
    public function getName(): string {
        return $this->propertyReflection->getName();
    }

    public function getPropertyReflection(): \ReflectionProperty {
        return $this->propertyReflection;
    }

    public function allowsNull(): bool {
        $type = $this->propertyReflection->getType();
        if ($type === null) {
            return false;
        }

        return $type->allowsNull();
    }

    public function isRequired(): bool
    {
        $notRequiredAttribute = Attributes::getSingleAttributeInstance($this->propertyReflection, NotRequired::class);
        return !$notRequiredAttribute instanceof NotRequired;
    }

    public function getDescription(): string {
        $description = null;

        if (!$this->isRequired() && !$this->allowsNull()) {
            $description .= '`!NOT-NULL!`';
        }

        $description .= $this->fieldAttribute->getDescription();

        return $description;
    }

    public function getFieldArgs(): array {
        if (isset($this->fieldArgs) === false) {
            $this->parseFieldArgs();
        }

        return $this->fieldArgs;
    }

    public function getResolverMethodDefinition(): ?array {
        return $this->resolverMethodDefinition;
    }

	public function getAllFieldAttributes(): array {
		return Attributes::getAttributeInstances($this->propertyReflection);
	}

    private function getResolverMethod(): ?\ReflectionMethod {
        return $this->resolverMethodReflection ?? $this->resolverMethodReflection = $this->extractResolverMethod();
    }

    // MID-LEVEL
    private function parseDefaultValue(\ReflectionProperty|\ReflectionParameter $property) {
        $defaultValue = null;

        try {
            $defaultValue = $property->getDefaultValue();
        } catch (\ReflectionException $e) {
        }

        if ($defaultValue instanceof \UnitEnum) {
            return $defaultValue->value;
        }

        return $defaultValue;
    }

    private function parseFieldArgs(): void {
        $reflectionClass = $this->typeReflectionClass;

        $resolverMethod = $this->getResolverMethod();

        if ($resolverMethod === null) {
            $this->fieldArgs = [];
            $this->resolverMethodDefinition = null;
            return;
        }

        $args = [];

        $methodParams = $resolverMethod->getParameters();

        $resolveInfoName = null;
        $objectValueName = null;

        foreach ($methodParams as $methodParam) {
            $paramType = $methodParam->getType();

            $paramName = $methodParam->getName();

            // has to have some paramType
            assert($paramType !== null);

            $paramTypeName = $paramType->getName();

            if (is_a($reflectionClass->getName(), $paramTypeName, true)) {
                // not a field arg, but the entity object
                $objectValueName = $paramName;
                continue;
            }

            if ($paramTypeName === ResolveInfo::class) {
                // not a field arg, but ResolveInfo
                $resolveInfoName = $paramName;
                continue;
            }

            // todo implement resolve info
            $definition = [
                'type' => $this->extractFieldType($methodParam), // todo implement ObjectType vs InputObjectType validation
                'description' => null, // todo
            ];

            if ($methodParam->isDefaultValueAvailable()) {
                $definition['defaultValue'] = $this->parseDefaultValue($methodParam);
            }

            $args[$paramName] = $definition;
        }

        // has to have objectValue arg
        assert($objectValueName !== null);

        $this->resolverMethodDefinition = [
            'className'          => $this->resolverReflection->getName(),
            'method'             => $resolverMethod->getShortName(),
            'resolveInfoArgName' => $resolveInfoName,
            'objectValueArgName' => $objectValueName,
        ];

        $this->fieldArgs = $args;
    }

    private function extractResolverMethod(): ?\ReflectionMethod {
        $propertyReflection = $this->propertyReflection;
        $resolverReflection = $this->resolverReflection;

        if ($resolverReflection === null) {
            return null;
        }

        $propertyName = $propertyReflection->getName();

        $methodName = self::fieldToResolverMethod($propertyName);

        $resolverMethodReflection = null;

        try {
            $resolverMethodReflection = $resolverReflection->getMethod($methodName);
        } catch (\ReflectionException $e) {

        }

        return $resolverMethodReflection;
    }

    private function extractFieldType(
        \ReflectionParameter|\ReflectionProperty $field
    ): Literal {
        $innerMostPHPType = $this->extractInnerMostPHPType($field);
        $innerMostGQLType = $this->classTypeToGQLType($innerMostPHPType);

		$scalarAliasAttribute = Attributes::getSingleAttributeInstance($field, ScalarAlias::class);
		if ($scalarAliasAttribute !== null) {
			$innerMostGQLType = $scalarAliasAttribute->getName();
		}

        $innerMostLiteral = new Literal('$this->get(?)', [$innerMostGQLType]);

        return $this->wrapInnerMostType($field, $innerMostLiteral);
    }

    private function wrapInnerMostType(
        \ReflectionParameter|\ReflectionProperty $field,
        Literal $innerMostLiteral
    ): Literal {
        $type = $field->getType();

        if ($type === null) {
            throw new \InvalidArgumentException('You must specify type for field.');
        }

        $resultLiteral = $innerMostLiteral;

        if ($type instanceof \ReflectionNamedType && $type->getName() === 'array') {
            // we do not allow individual items in array to be null for now... doesn't really make sense anyway
            $resultLiteral = new Literal('$this->NonNull(?)', [$resultLiteral]);
            // wrap as List since it's array
            $resultLiteral = new Literal('$this->List(?)', [$resultLiteral]);
        }

        if ($type->allowsNull() === false && $this->isRequired()) {
            // is the field non-nullable?
            $resultLiteral = new Literal('$this->NonNull(?)', [$resultLiteral]);
        }

        return $resultLiteral;
    }

    private function classTypeToGQLType(string $classType): string
    {
        $scalarType = TypeDefinitionProvider::getScalarDefinitionByValueType($classType);

        if ($scalarType !== null) {
            return $scalarType['name'];
        }

        return ($this->nameGenerator)(new \ReflectionClass($classType));
    }

    private function extractInnerMostPHPType(
        \ReflectionParameter|\ReflectionProperty $field
    ): string {
        $type = $field->getType();

        if ($type === null) {
            throw new \InvalidArgumentException('You must specify type for field.');
        }

        if ($type instanceof \ReflectionUnionType) {
            // union type is defined in separate attribute
            /** @var Unions|null $unionsAttribute */
            $unionsAttribute = Attributes::getSingleAttributeInstance($field, Unions::class);

            if ($unionsAttribute === null) {
                throw new \InvalidArgumentException('You must specify Unions annotation for union type.');
            }

            return $unionsAttribute->getUnionType();
        }

        $typeName = $type->getName();

        if ($typeName  === 'array') {
            // the inner ListOf type is defined in separate attribute
            /** @var ListOf|null $listOfAttribute */
            $listOfAttribute = Attributes::getSingleAttributeInstance($field, ListOf::class);

            if ($listOfAttribute === null) {
                throw new \InvalidArgumentException('You must specify ListOf attribute for array type.');
            }

            return $listOfAttribute->getType();
        }

        return $type->getName();
    }


    // LOW-LEVEL
    private static function fieldToResolverMethod(string $fieldName): string {
        return self::RESOLVER_METHOD_PREFIX . Strings::firstUpper($fieldName);
    }

    private static function resolverMethodToField(string $resolveMethodName): string {
        return Strings::firstLower(
            Strings::after($resolveMethodName, self::RESOLVER_METHOD_PREFIX)
        );
    }

}
