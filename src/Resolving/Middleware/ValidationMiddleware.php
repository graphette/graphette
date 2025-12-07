<?php

namespace Graphette\Graphette\Resolving\Middleware;

use GraphQL\Type\Definition\ResolveInfo;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Graphette\Graphette\Exception\ValidationException;
use Graphette\Graphette\TypeRegistry\FieldInfo;

class ValidationMiddleware implements ResolverMiddleware {

	private ValidatorInterface $validator;

	public function __construct(ValidatorInterface $validator) {
		$this->validator = $validator;
	}

	public function __invoke(
		$objectValue,
		array $args,
		$context,
		ResolveInfo $resolveInfo,
		FieldInfo $fieldInfo,
		callable $next
	): void {
		$violationMap = [];

		foreach ($args as $key => $arg) {
			if (is_object($arg) === false) {
				// we do not validate scalar values atm
				continue;
			}

			$violationList = $this->validator->validate($arg);

			if (count($violationList) === 0) {
				continue;
			}

			$violationMap[$key] = $violationList;
		}

		if (count($violationMap) > 0) {
			throw new ValidationException($violationMap);
		}

		$next($objectValue, $args, $context, $resolveInfo, $fieldInfo);
	}


}
