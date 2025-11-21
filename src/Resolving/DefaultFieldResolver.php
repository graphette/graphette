<?php declare(strict_types=1);
/**
 * @author Lukáš Jelič
 */

namespace Graphette\Graphette\Resolving;


use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\WrappingType;
use Nette\DI\Container;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Graphette\Graphette\Exception\ValidationException;
use Graphette\Graphette\Schema\TypeLoader;
use Graphette\Graphette\TypeRegistry\TypeRegistry;
use Graphette\Graphette\TypeRegistry\TypeRegistryLoader;

class DefaultFieldResolver {

    private TypeRegistry $typeRegistry;

	private MiddlewareChain $middlewareChain;

	public function __construct(
        TypeRegistryLoader $typeRegistryLoader,
		MiddlewareChain $middlewareChain,
    ) {
        $this->typeRegistry = $typeRegistryLoader->load();
		$this->middlewareChain = $middlewareChain;
	}

    public function defaultFieldResolver($objectValue, array $args, $context, ResolveInfo $info)
    {
        $type = $info->fieldDefinition->getType();
        $gqlTypeName = $this->getTypeName($type);

        $autoInstantiated = $this->typeRegistry->getAutoInstantiateClass($gqlTypeName);

        if ($autoInstantiated !== null) {

            return new $autoInstantiated;

        }

        $parentType = $info->parentType;
        $parentTypeName = $this->getTypeName($parentType);

        $fieldName = $info->fieldName;

        $fieldInfo = $this->typeRegistry->getFieldInfo($parentTypeName, $fieldName);

		$this->middlewareChain->execute($objectValue, $args, $context, $info, $fieldInfo);

//        if ($fieldInfo !== null) {
//        }

        return $objectValue->$fieldName;
    }

    private function getTypeName(Type $type): string {
        if ($type instanceof WrappingType) {
            return $this->getTypeName($type->getInnermostType());
        }

        if ($type instanceof NamedType) {
            return $type->name();
        }

        return $type->toString();
    }


}
