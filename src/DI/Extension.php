<?php declare(strict_types=1);
/**
 * @author Lukáš Jelič
 */

namespace Graphette\Graphette\DI;


use GraphQL\Type\Definition\BooleanType;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\StringType;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Statement;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nette\Utils\Strings;
use Symfony\Component\Validator\ContainerConstraintValidatorFactory;
use Symfony\Component\Validator\Validation;
use Graphette\Graphette\Application\Application;
use Graphette\Graphette\Application\ServerConfigProvider;
use Graphette\Graphette\Error\DefaultErrorFormatter;
use Graphette\Graphette\Resolving\DefaultFieldResolver;
use Graphette\Graphette\Resolving\MiddlewareChain;
use Graphette\Graphette\Scalar\ScalarType;
use Graphette\Graphette\Schema\SchemaBuilder;
use Graphette\Graphette\TypeRegistry\TypeDefinitionProvider;
use Graphette\Graphette\TypeRegistry\TypeFinder;
use Graphette\Graphette\TypeRegistry\TypeName;
use Graphette\Graphette\TypeRegistry\TypeRegistryBuilderFactory;
use Graphette\Graphette\TypeRegistry\TypeRegistryLoader;
use Graphette\Graphette\Validation\SymfonySchemaConstraintsProvider;

class Extension extends CompilerExtension {

    public const DEFAULT_SCALAR_TYPES = [
        'int' => [
            'name' => 'Int',
            'className' => IntType::class,
            'valueType' => 'int',
            'builtIn' => true, // is built in webonyx/graphql-php... needs bridge to its Type class
        ],
        'float' => [
            'name' => 'Float',
            'className' => FloatType::class,
            'valueType' => 'float',
            'builtIn' => true,
        ],
        'string' => [
            'name' => 'String',
            'className' => StringType::class,
            'valueType' => 'string',
            'builtIn' => true,
        ],
        'boolean' => [
            'name' => 'Boolean',
            'className' => BooleanType::class,
            'valueType' => 'bool',
            'builtIn' => true,
        ],
        // todo ID TYPEs
    ];

    public function getConfigSchema(): Schema {
        $parameters = $this->getContainerBuilder()->parameters;

        return Expect::structure([
            'debugMode' => Expect::bool($parameters['debugMode'] ?? false),
            'tracyExceptionWithHeader' => Expect::bool(false),
            'rootNamespace' => Expect::string()->required(),
            'rootPath' => Expect::string()->required(),
            'tempDir' => Expect::string()->required(),
            'scalarTypes' => Expect::arrayOf(
                Expect::structure(
                    [
//                        'name' => Expect::string()->required(),
                        'className' => Expect::string()->required(),
						'aliases' => Expect::arrayOf(Expect::string())->default([]),
//                        'builtIn' => Expect::bool()->default(false),
                    ]
                )->castTo('array'),
                Expect::string()
            ),
			'resolverMiddlewares' => Expect::arrayOf(Expect::string()),
        ]);
    }

    public function loadConfiguration(): void {
		$this->registerResolverMiddlewares();

        $config = $this->config;

        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('application'))
            ->setFactory(Application::class);

        $builder->addDefinition($this->prefix('serverConfigProvider'))
            ->setFactory(ServerConfigProvider::class, [
                $config->debugMode,
                $config->tracyExceptionWithHeader,
            ]);

        $builder->addDefinition($this->prefix('type.finder'))
            ->setFactory(TypeFinder::class, [
                $config->rootPath,
                $config->tempDir,
            ]);

        $builder->addDefinition($this->prefix('type.name'))
            ->setFactory(TypeName::class, [
                $config->rootNamespace,
            ]);

        $builder->addDefinition($this->prefix('type.definitionProvider'))
            ->setFactory(TypeDefinitionProvider::class)
            ->addSetup('@self::setScalarTypeDefinitions', [$this->getRegisteredScalarDefinitions()]);

        $builder->addFactoryDefinition($this->prefix('type.registryBuilderFactory'))
            ->setImplement(TypeRegistryBuilderFactory::class)
//            ->getResultDefinition()x
//                ->setArguments([
//                    'scalarTypes' => $config->scalarTypes
//                ])
        ;

        $builder->addDefinition($this->prefix('type.registryLoader'))
            ->setFactory(TypeRegistryLoader::class, [
                $config->tempDir,
                $config->rootPath,
                $config->debugMode,
            ]);

        $builder->addDefinition($this->prefix('schema.builder'))
            ->setFactory(SchemaBuilder::class);

        $builder->addDefinition($this->prefix('resolving.defaultFieldResolver'))
            ->setFactory(DefaultFieldResolver::class);

        $builder->addDefinition($this->prefix('error.defaultErrorFormatter'))
            ->setFactory(DefaultErrorFormatter::class);

        $validatorBuilder = $builder->addDefinition($this->prefix('validation.validatorBuilder'))
            ->setFactory(Validation::class . '::createValidatorBuilder')
            ->addSetup('enableAnnotationMapping')
//            ->addSetup('setConstraintValidatorFactory', [new Statement(ContainerConstraintValidatorFactory::class)])
            ->setAutowired(false);

        $builder->addDefinition($this->prefix('validation.validator'))
            ->setFactory([$validatorBuilder, 'getValidator']);

//        $builder->addDefinition($this->prefix('validation.symfonySchemaConstraintProvider'))
//            ->setFactory(SymfonySchemaConstraintsProvider::class);

    }

	private function registerResolverMiddlewares(): void {
		$config = $this->config;

		$builder = $this->getContainerBuilder();

		$resolverMiddlewares = $config->resolverMiddlewares;

		$middlewareDefinitions = [];
		$count = 0;
		foreach ($resolverMiddlewares as $resolverMiddleware) {
			$middlewareDefinition = $builder->addDefinition($this->prefix('middleware_' . $count++))
				->setFactory($resolverMiddleware)
				->setAutowired(false);

			$middlewareDefinitions[] = $middlewareDefinition;
		}

		$builder->addDefinition($this->prefix('middlewareChain'))
			->setFactory(MiddlewareChain::class, [$middlewareDefinitions]);

	}

    private function getRegisteredScalarDefinitions(): array {
        $registeredScalars = $this->config->scalarTypes;
        $result = [];

        foreach ($registeredScalars as $scalarName => $registeredScalar) {
            $typeClassName = $registeredScalar['className'];

            if (is_subclass_of($typeClassName, ScalarType::class) === false) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Scalar type "%s" must be a subclass of "%s".',
                        $typeClassName,
                        ScalarType::class
                    )
                );
            }

            $result[$scalarName] = [
                'name' => $scalarName,
                'className' => $typeClassName,
				'aliases' => $registeredScalar['aliases'] ?? [],
                'valueType' => $typeClassName::getType(),
                'builtIn' => false,
            ];
        }

        return array_merge($result, self::DEFAULT_SCALAR_TYPES);
    }

}
