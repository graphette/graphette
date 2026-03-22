<?php declare(strict_types=1);
/**
 * @author Lukáš Jelič
 */

namespace Graphette\Graphette\Directive;

use GraphQL\Type\Definition\Directive;

abstract class DirectiveType extends Directive {

	public function __construct() {
		parent::__construct([
			'name' => static::getName(),
			'description' => static::getDescription(),
			'locations' => static::getLocations(),
			'args' => static::getArgs(),
			'isRepeatable' => static::isRepeatable(),
		]);
	}

	abstract public static function getName(): string;

	abstract public static function getDescription(): ?string;

	/** @return array<string> DirectiveLocation constants */
	abstract public static function getLocations(): array;

	/** @return array<string, mixed> */
	public static function getArgs(): array {
		return [];
	}

	public static function isRepeatable(): bool {
		return false;
	}

}
