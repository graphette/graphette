<?php

namespace Graphette\Graphette\Scalar;

use GraphQL\Language\AST\Node;
use Graphette\Graphette\Scalar\Value\Date;
use Graphette\Graphette\Scalar\Value\Time;

class TimeType extends ScalarType {

    public static function getType(): string {
        return Time::class;
    }

    // eg. 2023-08-24
    private const TIME_FORMAT = 'H:i:s';

    public string $name = 'Time';

    public ?string $description = 'The `Time` scalar type represents date in PHP format `H:i:s` (eg. 4:20:00)';

    /**
     * Called when converting internal representation of value returned by your app (e.g. stored in database or hardcoded in source code) to serialized
     * representation included in response.
     */
    public function serialize($value): string {
        if(is_string($value)) {
            $value = new Time($value);
        }

        if (!$value instanceof Time) {
            throw new \UnexpectedValueException("Cannot represent value as {$this->name}: " . \GraphQL\Utils\Utils::printSafe($value));
        }

        return $value->format(self::TIME_FORMAT);
    }

    /**
     * Called when converting input value passed by client in variables along with GraphQL query to internal representation of your app.
     */
    public function parseValue($value): Time {
        return Time::createFromFormat(self::TIME_FORMAT, $value);
    }

    /**
     * Called when converting input literal value hardcoded in GraphQL query (e.g. field argument value) to internal representation of your app.
     *
     * E.g.: { someQuery(dateTime: "Tue Feb 21 2017 17:31:44 GMT+0100 (CET)") }
     *
     * @param \GraphQL\Language\AST\Node $valueNode
     */
    public function parseLiteral(Node $valueNode, array $variables = null): Time {
        // Note: throwing GraphQL\Error\Error vs \UnexpectedValueException to benefit from GraphQL
        // error location in query:
        if (!$valueNode instanceof \GraphQL\Language\AST\StringValueNode) {
            throw new \GraphQL\Error\Error('Query error: Can only parse strings got: ' . $valueNode->kind, [$valueNode]);
        }

        return $this->parseValue($valueNode->value);
    }

}
