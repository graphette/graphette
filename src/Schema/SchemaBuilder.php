<?php declare(strict_types=1);
/**
 * @author Lukáš Jelič
 */

namespace Graphette\Graphette\Schema;


use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use Graphette\Graphette\Schema\Exception\InvalidSchemaException;
use Graphette\Graphette\TypeRegistry\TypeRegistryLoader;

class SchemaBuilder {

    private TypeRegistryLoader $typeRegistryLoader;

    public function __construct(
        TypeRegistryLoader $typeRegistryLoader
    ) {
        $this->typeRegistryLoader = $typeRegistryLoader;
    }

    // HIGH-LEVEL

    /**
     * @throws InvalidSchemaException
     */
    public function build(): Schema {
        $typeRegistry = $this->typeRegistryLoader->load();

        if ($typeRegistry->has('Query') === false) {
            throw new InvalidSchemaException('Query type is not defined. You need to define at least one ...Queries type.');
        }

        $schemaConfig = [
            'query' => $typeRegistry->get('Query'),
            'typeLoader' => fn (string $name): Type => $typeRegistry->get($name),
            'types' => $typeRegistry->getInvisibleTypes(),
        ];

        if ($typeRegistry->has('Mutation')) {
            $schemaConfig['mutation'] = $typeRegistry->get('Mutation');
        }

        return new Schema($schemaConfig);
    }



}
