<?php

namespace Graphette\Graphette\Resolving\Middleware;

use GraphQL\Type\Definition\ResolveInfo;
use Graphette\Graphette\TypeRegistry\FieldInfo;

interface ResolverMiddleware {

	public function __invoke(
		$objectValue,
		array $args,
		$context,
		ResolveInfo $resolveInfo,
		FieldInfo $fieldInfo,
		callable $next
	): void;

}
