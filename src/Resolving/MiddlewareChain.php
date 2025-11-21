<?php

namespace Graphette\Graphette\Resolving;

use GraphQL\Type\Definition\ResolveInfo;
use Graphette\Graphette\Resolving\Middleware\ResolverMiddleware;
use Graphette\Graphette\TypeRegistry\FieldInfo;

class MiddlewareChain {

	/**
	 * @var ResolverMiddleware[]
	 */
	private array $middlewares;

	/**
	 * @param array<ResolverMiddleware> $middlewares
	 */
	public function __construct(array $middlewares) {
		$this->middlewares = $middlewares;
	}

	public function create(): callable {
		$next = function ($objectValue, array $args, $context, ResolveInfo $resolveInfo, FieldInfo $fieldInfo): void {

		};

		$middlewares = $this->middlewares;
		while ($middleware = array_pop($middlewares)) {
			$next = function ($objectValue, array $args, $context, ResolveInfo $resolveInfo, FieldInfo $fieldInfo) use ($middleware, $next): void {
				$middleware($objectValue, $args, $context, $resolveInfo, $fieldInfo, $next);
			};
		}

		return $next;
	}

	public function execute($objectValue, array $args, $context, ResolveInfo $resolveInfo, FieldInfo $fieldInfo): void {
		($this->create())($objectValue, $args, $context, $resolveInfo, $fieldInfo);
	}

}
