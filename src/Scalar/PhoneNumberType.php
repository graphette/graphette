<?php

namespace Graphette\Graphette\Scalar;

use GraphQL\Error\Error;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Utils\Utils;
use Graphette\Graphette\Scalar\Value\Exception\InvalidPhoneNumber;
use Graphette\Graphette\Scalar\Value\PhoneNumber;

class PhoneNumberType extends ScalarType
{
	public string $name = 'PhoneNumber';
	public ?string $description = 'The `PhoneNumber` scalar type represents a valid phone number.';

	/**
	 * Called when converting internal representation of value returned by your app (e.g. stored in database or hardcoded in source code) to serialized
	 * representation included in response.
	 */
	public function serialize(mixed $value): string
	{
		if (!$value instanceof PhoneNumber) {
			throw new \UnexpectedValueException("Cannot represent value as {$this->name}: " . Utils::printSafe($value));
		}

		return $value->getPhoneNumber();
	}

	/**
	 * Called when converting input value passed by client in variables along with GraphQL query to internal representation of your app.
	 */
	public function parseValue(mixed $value): PhoneNumber
	{
		try {
			$phoneNumber = new PhoneNumber($value);
		} catch (InvalidPhoneNumber|\TypeError) {
			throw new \UnexpectedValueException('Not a valid phone number format.');
		}

		return $phoneNumber;
	}

	/**
	 * Called when converting input literal value hardcoded in GraphQL query (e.g. field argument value) to internal representation of your app.
	 *
	 * E.g.: { someQuery(dateTime: "Tue Feb 21 2017 17:31:44 GMT+0100 (CET)") }
	 *
	 * @param Node $valueNode
	 * @param array|null $variables
	 * @return PhoneNumber
	 * @throws Error
	 */
	public function parseLiteral(Node $valueNode, array $variables = null): PhoneNumber
	{
		// Note: throwing GraphQL\Error\Error vs \UnexpectedValueException to benefit from GraphQL
		// error location in query:
		if (!$valueNode instanceof StringValueNode) {
			throw new Error('Query error: Can only parse strings got: ' . $valueNode->kind, [$valueNode]);
		}

		return $this->parseValue($valueNode->value);
	}

	public static function getType(): string
	{
		return PhoneNumber::class;
	}
}
