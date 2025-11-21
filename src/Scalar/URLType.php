<?php

namespace Graphette\Graphette\Scalar;

use GraphQL\Error\Error;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Utils\Utils;
use Graphette\Graphette\Scalar\Value\Exception\InvalidURL;
use Graphette\Graphette\Scalar\Value\URL;

class URLType extends ScalarType
{
	public string $name = 'URL';
	public ?string $description = 'The `URL` scalar type represents a valid URL.';

	/**
	 * Called when converting internal representation of value returned by your app (e.g. stored in database or hardcoded in source code) to serialized
	 * representation included in response.
	 */
	public function serialize(mixed $value): string
	{
		if (!$value instanceof URL) {
			throw new \UnexpectedValueException("Cannot represent value as {$this->name}: " . Utils::printSafe($value));
		}

		return $value->getUrl();
	}

	/**
	 * Called when converting input value passed by client in variables along with GraphQL query to internal representation of your app.
	 */
	public function parseValue(mixed $value): URL
	{
		try {
			$url = new URL($value);
		} catch (InvalidURL|\TypeError) {
			throw new \UnexpectedValueException('Not a valid URL format.');
		}

		return $url;
	}

	/**
	 * Called when converting input literal value hardcoded in GraphQL query (e.g. field argument value) to internal representation of your app.
	 *
	 * E.g.: { someQuery(dateTime: "Tue Feb 21 2017 17:31:44 GMT+0100 (CET)") }
	 *
	 * @param Node $valueNode
	 * @param array|null $variables
	 * @return URL
	 * @throws Error
	 */
	public function parseLiteral(Node $valueNode, array $variables = null): URL
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
		return URL::class;
	}
}
