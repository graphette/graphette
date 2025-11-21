<?php

namespace Graphette\Graphette\Exception;

use GraphQL\Error\ClientAware;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends \Exception implements ClientAware {

    /**
     * @var ConstraintViolationListInterface[]
     */
    private array $violationMap;

    /**
     * @param array<string, ConstraintViolationListInterface> $violationMap
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(array $violationMap, string $message = "", int $code = 0, ?Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->violationMap = $violationMap;
    }

    public function getViolationMap(): array {
        return $this->violationMap;
    }

    public function isClientSafe(): bool {
        return true;
    }

}
