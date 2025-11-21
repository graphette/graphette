<?php

namespace Graphette\Graphette\TypeRegistry\Definition;

use Nette\PhpGenerator\Dumper;
use Nette\PhpGenerator\Literal;
use ReflectionException;
use Graphette\Graphette\Attribute\ContainsFields;
use Graphette\Graphette\Attribute\EnumType;
use Graphette\Graphette\Attribute\Field;
use Graphette\Graphette\Attribute\InputObjectType;
use Graphette\Graphette\Attribute\InterfaceType;
use Graphette\Graphette\Attribute\ObjectType;
use Graphette\Graphette\Attribute\ResolvesType;
use Graphette\Graphette\Attribute\Type;
use Graphette\Graphette\Attribute\UnionType;
use Graphette\Graphette\Utils\Attributes;
use Graphette\Graphette\Utils\Exception\AttributesException;

class ClassTypeDefinition extends TypeDefinition {

    private \ReflectionClass $classReflection;

    private Type $typeAttribute;

    private \Closure $nameGenerator;

    private Dumper $dumper;

    /** @var array<PropertyFieldDefinition> */
    private array $fieldDefinitions;

    private bool $autoInstantiate = false;

    public function __construct(
        string $name,
        \ReflectionClass $classReflection,
        Type $typeAttribute,
        \Closure $nameGenerator,
    ) {
        parent::__construct($name);
        $this->classReflection = $classReflection;
        $this->typeAttribute = $typeAttribute;
        $this->nameGenerator = $nameGenerator;

        $this->dumper = new Dumper();
    }

    // HIGH-LEVEL
    public function printTypeFactoryMethodBody(): string {
        $classReflection = $this->classReflection;
        $typeAttribute = $this->typeAttribute;

        $definition = [
            'name' => $this->getName(),
            'description' => $typeAttribute->getDescription(),
        ];

        if ($this->isUnionType()) {
            $this->addUnionTypesToDefinition($definition, $typeAttribute->getTypes());
        }

        if ($this->isImplementingInterfaces()) {
            $typeInterfaces = self::getClassInterfaces($classReflection);
            $this->verifyInterfaceAndTraitPairs($classReflection, $typeInterfaces);
            $this->addImplementToDefinition($definition, $typeInterfaces);
        }

        if ($this->isContainingFields()) {
            $this->addFieldsToDefinition($definition);
        }

        if ($this->isResolvingType()) {
            $this->addResolveTypeToDefinition($definition);
        }

        if ($this->isInputObjectType()) {
            $this->addInputObjectParseValue($definition, $classReflection);
        }

        if ($this->isEnumType()) {
            $this->addEnumValues($definition, $classReflection);
        }

        $definitionDump = $this->dumper->dump($definition);
        $returnNewInstance = "return new {$typeAttribute->getQraphQLTypeClassName()}(\n\t%s\n);";

        return sprintf($returnNewInstance, $definitionDump);
    }

    // GETTERS AND SETTERS
    public function getFullClassName(): string {
        return $this->classReflection->getName();
    }

    public function getShortClassName(): string {
        return $this->classReflection->getShortName();
    }

    /**
     * @return array<PropertyFieldDefinition>
     */
    public function getFieldDefinitions(): array {
        return $this->fieldDefinitions ??= $this->parseFieldDefinitions();
    }

    /**
     * @return bool
     */
    public function isAutoInstantiate(): bool {
        return $this->autoInstantiate;
    }

    /**
     * @param bool $autoInstantiate
     */
    public function setAutoInstantiate(bool $autoInstantiate = true): void {
        $this->autoInstantiate = $autoInstantiate;
    }

    public function isUnionType(): bool {
        return $this->typeAttribute instanceof UnionType;
    }

    public function isImplementingInterfaces(): bool {
        return self::hasClassInterfaces($this->classReflection);
    }

    public function isContainingFields(): bool {
        return $this->typeAttribute instanceof ContainsFields;
    }

    public function isResolvingType(): bool {
        return $this->typeAttribute instanceof ResolvesType;
    }

    public function isInputObjectType(): bool {
        return $this->typeAttribute instanceof InputObjectType;
    }

    public function isEnumType(): bool {
        return $this->typeAttribute instanceof EnumType;
    }

    // MID-LEVEL

    /**
     * @return array<PropertyFieldDefinition>
     * @throws ReflectionException
     * @throws AttributesException
     */
    private function parseFieldDefinitions(): array {
        $fieldDefinitions = [];

        $classReflection = $this->classReflection;
        $typeAttribute = $this->typeAttribute;

        if ($this->classReflection->isInterface()) {
            assert($typeAttribute instanceof InterfaceType);

            $classReflection = new \ReflectionClass($typeAttribute->getAttachedTrait());
        }

        $properties = $classReflection->getProperties();

        foreach ($properties as $property) {
            /** @var Field $fieldAttribute */
            $fieldAttribute = Attributes::getSingleAttributeInstance($property, Field::class);

            if ($fieldAttribute === null) {
                continue;
            }

            $fieldDefinitions[] = new PropertyFieldDefinition($this->classReflection, $property, $fieldAttribute, $this->nameGenerator, $this->getResolverReflectionClass());
        }

        return $fieldDefinitions;
    }

    private function getResolverReflectionClass(): ?\ReflectionClass {
        if (!$this->typeAttribute instanceof ObjectType) {
            return null;
        }

        $resolverClass = $this->typeAttribute->getResolver();

        if ($resolverClass === null) {
            return null;
        }

        return new \ReflectionClass($resolverClass);
    }

    private function addEnumValues(array &$definition, \ReflectionClass $type): void {
        if (!$type instanceof \ReflectionEnum) {
            throw new \InvalidArgumentException('Enum type must be enum');
        }

        $cases = $type->getCases();

        foreach ($cases as $case) {
            $definition['values'][$case->getName()] = [
                'value' => $case->getValue(),
            ];
        }
    }

    private function addInputObjectParseValue(array &$definition, \ReflectionClass $type): void {
        $definition['parseValue'] = new Literal('fn(array $args) => $this->parseInputObject($args, ?)', [$type->getName()]);
    }

    private function addResolveTypeToDefinition(array &$definition): void {
        $definition['resolveType'] = new Literal('[$this, \'resolveObjectType\']');
    }

    private function addUnionTypesToDefinition(array &$definition, array $types): void {
        // todo add validation for colliding types in annotation and php union type definition
        $typeDefinitions = [];

        foreach ($types as $type) {
            $typeName = ($this->nameGenerator)(new \ReflectionClass($type));
            $typeDefinitions[] = new Literal('$this->get(?)', [$typeName]);
        }

        $definition['types'] = new Literal('fn() => ?', [$typeDefinitions]);
    }

    private function verifyInterfaceAndTraitPairs(\ReflectionClass $type, array $interfaces): void {
        $traits = $type->getTraitNames();

        foreach ($interfaces as $interface) {
            /** @var InterfaceType $interfaceAttribute */
            $interfaceAttribute = Attributes::getSingleAttributeInstance($interface, InterfaceType::class);

            $traitName = $interfaceAttribute->getAttachedTrait();

            if (in_array($traitName, $traits, true) === false) {
                throw new \LogicException(sprintf('Interface %s requires usage of trait %s in ObjectType %s', $interface->getName(), $traitName, $type->getName()));
            }

        }
    }

    private function addImplementToDefinition(array &$definition, array $interfaces): void {
        $interfaceDefinitions = [];

        foreach ($interfaces as $interface) {
            $interfaceName = ($this->nameGenerator)($interface);
            $interfaceDefinitions[] = new Literal('$this->get(?)', [$interfaceName]);
        }

        $definition['interfaces'] = new Literal('fn() => ?', [$interfaceDefinitions]);
    }

    private function addFieldsToDefinition(array &$definition): void {
        $fields = [];

        $fieldDefinitions = $this->getFieldDefinitions();

        foreach ($fieldDefinitions as $fieldDefinition) {
            $fields[$fieldDefinition->getName()] = $fieldDefinition->extractFieldDefinition();
        }

        $definition['fields'] = new Literal('fn() => ?', [$fields]);

    }

    // LOW-LEVEL
    private static function hasClassInterfaces(\ReflectionClass $type): bool {
        return count(self::getClassInterfaces($type)) > 0;
    }

    private static function getClassInterfaces(\ReflectionClass $class): array {
        $result = [];

        $interfaces = $class->getInterfaces();

        foreach ($interfaces as $interface) {
            $interfaceAttribute = Attributes::getSingleAttributeInstance($interface, InterfaceType::class);

            if ($interfaceAttribute === null) {
                continue;
            }

            $result[] = $interface;
        }

        return $result;

    }

}
