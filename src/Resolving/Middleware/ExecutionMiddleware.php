<?php

namespace Graphette\Graphette\Resolving\Middleware;

use GraphQL\Type\Definition\ResolveInfo;
use Nette\DI\Container;
use Graphette\Graphette\TypeRegistry\FieldInfo;

class ExecutionMiddleware implements ResolverMiddleware {

	private Container $container;

	public function __construct(Container $container) {

		$this->container = $container;
	}

	public function __invoke(
		$objectValue,
		array $args,
		$context,
		ResolveInfo $resolveInfo,
		FieldInfo $fieldInfo,
		callable $next
	): void {
		$resolverMethod = $fieldInfo->getResolveMethod();

		if ($resolverMethod === null) {
			$next($objectValue, $args, $context, $resolveInfo, $fieldInfo);
			return;
		}

		$resolverService = $this->container->getByType($resolverMethod['className']);

		$args[$resolverMethod['objectValueArgName']] = $objectValue;

		if ($resolverMethod['resolveInfoArgName'] !== null) {
			$args[$resolverMethod['resolveInfoArgName']] = $resolveInfo;
		}

		$resolverService->{$resolverMethod['method']}(...$args);

		$next($objectValue, $args, $context, $resolveInfo, $fieldInfo);
	}


}
