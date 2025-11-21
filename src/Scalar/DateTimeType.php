<?php declare(strict_types=1);
/**
 * @author Lukáš Jelič
 */

namespace Graphette\Graphette\Scalar;


use GraphQL\Error\Error;
use GraphQL\Error\SerializationError;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\ValueNode;

class DateTimeType extends ScalarType {

    public static function getType(): string {
        return \DateTime::class;
    }

    // eg. 2023-08-24T14:30:45.123456+03:00
    private const JS_ATOM = 'Y-m-d\TH:i:s.uP';

    public string $name = 'DateTime';

    public ?string $description = 'The `DateTime` scalar type represents date/time format compatible with ISO 8601 format.';

    /**
     * Called when converting internal representation of value returned by your app (e.g. stored in database or hardcoded in source code) to serialized
     * representation included in response.
     */
    public function serialize($value): string {
        if(is_string($value)) {
            $value = new \DateTime($value);
        }
        if(is_int($value)) {
            $value = (new \DateTime())->setTimestamp($value);
        }
        if (!$value instanceof \DateTimeInterface) {
            throw new \UnexpectedValueException("Cannot represent value as {$this->name}: " . \GraphQL\Utils\Utils::printSafe($value));
        }
        return $value->format(self::JS_ATOM);
    }

    /**
     * Called when converting input value passed by client in variables along with GraphQL query to internal representation of your app.
     */
    public function parseValue($value): \DateTimeInterface {
        //NOTE: DATE_ISO8601 format is not compatible with ISO-8601, but is left this way in PHP for backward compatibility reasons.
        //  http://php.net/manual/en/class.datetime.php#datetime.constants.types
        $dateTime = \DateTime::createFromFormat(\DateTime::ATOM, $value);
        if ($dateTime === false) { // compatibility with Javascript
            $dateTime = \DateTime::createFromFormat(self::JS_ATOM, $value);
        }
        if ($dateTime === false) {
            throw new \UnexpectedValueException('Not a valid ISO 8601 date format.');
        }
        // always convert to UTC timezone for easier internal usage
        $dateTime = $dateTime->setTimezone(new \DateTimeZone('UTC'));
        return $dateTime;
    }

    /**
     * Called when converting input literal value hardcoded in GraphQL query (e.g. field argument value) to internal representation of your app.
     *
     * E.g.: { someQuery(dateTime: "Tue Feb 21 2017 17:31:44 GMT+0100 (CET)") }
     *
     * @param \GraphQL\Language\AST\Node $valueNode
     */
    public function parseLiteral(Node $valueNode, array $variables = null): \DateTimeInterface {
        // Note: throwing GraphQL\Error\Error vs \UnexpectedValueException to benefit from GraphQL
        // error location in query:
        if (!$valueNode instanceof \GraphQL\Language\AST\StringValueNode) {
            throw new \GraphQL\Error\Error('Query error: Can only parse strings got: ' . $valueNode->kind, [$valueNode]);
        }

        return $this->parseValue($valueNode->value);
    }

}
