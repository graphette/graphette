<?php

namespace Graphette\Graphette\Scalar;

use GraphQL\Language\AST\Node;
use GraphQL\Utils\Utils;
use Graphette\Graphette\Scalar\Value\Email;
use Graphette\Graphette\Scalar\Value\Exception\InvalidEmail;
use Graphette\Graphette\Scalar\Value\JSON;

class JSONType extends ScalarType {

    public static function getType(): string {
        return JSON::class;
    }

    public string $name = 'JSON';

    public ?string $description = 'The `JSON` scalar type represents a valid json string.';

    /**
     * Called when converting internal representation of value returned by your app (e.g. stored in database or hardcoded in source code) to serialized
     * representation included in response.
     */
    public function serialize($value): string {
        if (!$value instanceof JSON) {
            throw new \UnexpectedValueException("Cannot represent value as {$this->name}: " . Utils::printSafe($value));
        }

        return (string)  $value;
    }

    /**
     * Called when converting input value passed by client in variables along with GraphQL query to internal representation of your app.
     */
    public function parseValue($value): JSON {
            $json = JSON::createFromString($value);

        return $json;
    }

    /**
     * Called when converting input literal value hardcoded in GraphQL query (e.g. field argument value) to internal representation of your app.
     *
     * E.g.: { someQuery(dateTime: "Tue Feb 21 2017 17:31:44 GMT+0100 (CET)") }
     *
     * @param \GraphQL\Language\AST\Node $valueNode
     */
    public function parseLiteral(Node $valueNode, array $variables = null): JSON {
        // Note: throwing GraphQL\Error\Error vs \UnexpectedValueException to benefit from GraphQL
        // error location in query:
        if (!$valueNode instanceof \GraphQL\Language\AST\StringValueNode) {
            throw new \GraphQL\Error\Error('Query error: Can only parse strings got: ' . $valueNode->kind, [$valueNode]);
        }

        return $this->parseValue($valueNode->value);
    }

}
