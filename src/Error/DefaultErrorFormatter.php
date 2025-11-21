<?php

namespace Graphette\Graphette\Error;

use GraphQL\Error\Error;
use Nette\Utils\Strings;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Graphette\Graphette\Exception\ApplicationException;
use Graphette\Graphette\Exception\ValidationException;
use Graphette\Graphette\Validation\SymphonyConstraintSerializer;
use Tracy\Debugger;
use Tracy\ILogger;

class DefaultErrorFormatter {

    public const ERROR_TYPE_SERVER = 'server';
    public const ERROR_TYPE_APPLICATION = 'application';
    public const ERROR_TYPE_VALIDATION = 'validation';
    public const ERROR_TYPE_SCHEMA = 'schema';

    public function formatError(Error $error): array {
		// todo switch off on dev & maybe tidy this up? Kinda crude... really needs a proper fix
		if (
			($error->getPrevious() === null || $error->getPrevious() instanceof Error)
			&& $error instanceof \JsonSerializable
		) {
			return array_merge([
				'type' => self::ERROR_TYPE_SCHEMA,
			], $error->jsonSerialize());
		}

        $formattedError = [
            'type' => self::evaluateErrorType($error),
            'path' => self::extractPathString($error),
        ];

        if ($error->getPrevious() instanceof ApplicationException) {
            $formattedError['id'] = $error->getPrevious()->getErrorId();
        }

        if ($error->getPrevious() instanceof ValidationException) {
            $formattedError['arguments'] = self::convertRuleViolations($error->getPrevious()->getViolationMap());
        }

        if ($formattedError['type'] === self::ERROR_TYPE_SERVER) {
            Debugger::log($error->getPrevious(), ILogger::EXCEPTION);
        }

        return $formattedError;
    }

    private static function evaluateErrorType(Error $error): string {
        if ($error->isClientSafe() === true
            && $error->getPrevious() instanceof ApplicationException) {
            return self::ERROR_TYPE_APPLICATION;
        }

        if ($error->getPrevious() instanceof ValidationException) {
            return self::ERROR_TYPE_VALIDATION;
        }

        return self::ERROR_TYPE_SERVER;

    }

    private static function extractPathString(Error $error): string {
        $pathParts = $error->getPath();
        $pathString = '';

        if (is_iterable($pathParts)) {
            foreach ($pathParts as $pathPart) {
                if (is_int($pathPart)) {
                    $pathString .= '[' . $pathPart . ']';
                } else {
                    $pathString .= '.' . $pathPart;
                }
            }
        }

        return Strings::trim($pathString, '.');

    }

    /**
     * @param array<string, ConstraintViolationListInterface> $violationMap
     * @return array
     * @throws \Exception
     */
    private static function convertRuleViolations(array $violationMap): array {
        $convertedViolations = [];

        foreach ($violationMap as $arg => $violationList) {
            foreach ($violationList as $violation) {
                $convertedViolations[$arg . '.' . $violation->getPropertyPath()][] = SymphonyConstraintSerializer::serialize($violation->getConstraint());
            }
        }

        return $convertedViolations;
    }

}
