<?php

namespace Graphette\Graphette\Scalar;

use GraphQL\Language\AST\Node;
use Graphette\Graphette\Scalar\Value\Date;

class DateType extends ScalarType {

    public static function getType(): string {
        return Date::class;
    }

    // eg. 2023-08-24
    private const DATE_FORMAT = 'Y-m-d';

    public string $name = 'Date';

    public ?string $description = 'The `Date` scalar type represents date in PHP format `Y-m-d` (eg. 2023-08-24)';

    /**
     * Called when converting internal representation of value returned by your app (e.g. stored in database or hardcoded in source code) to serialized
     * representation included in response.
     */
    public function serialize($value): string {
        if(is_string($value)) {
            $value = new Date($value);
        }

        if (!$value instanceof Date) {
            throw new \UnexpectedValueException("Cannot represent value as {$this->name}: " . \GraphQL\Utils\Utils::printSafe($value));
        }

        return $value->format(self::DATE_FORMAT);
    }

    /**
     * Called when converting input value passed by client in variables along with GraphQL query to internal representation of your app.
     */
    public function parseValue($value): Date {
        $dateTime = Date::createFromFormat(self::DATE_FORMAT, $value);
		b($dateTime);

		return $dateTime;
    }

    /**
     * Called when converting input literal value hardcoded in GraphQL query (e.g. field argument value) to internal representation of your app.
     *
     * E.g.: { someQuery(dateTime: "Tue Feb 21 2017 17:31:44 GMT+0100 (CET)") }
     *
     * @param \GraphQL\Language\AST\Node $valueNode
     */
    public function parseLiteral(Node $valueNode, array $variables = null): Date {
        // Note: throwing GraphQL\Error\Error vs \UnexpectedValueException to benefit from GraphQL
        // error location in query:
        if (!$valueNode instanceof \GraphQL\Language\AST\StringValueNode) {
            throw new \GraphQL\Error\Error('Query error: Can only parse strings got: ' . $valueNode->kind, [$valueNode]);
        }

        return $this->parseValue($valueNode->value);
    }

}
