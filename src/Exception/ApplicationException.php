<?php

namespace Graphette\Graphette\Exception;

use GraphQL\Error\ClientAware;

class ApplicationException extends \Exception implements ClientAware {

    private string $errorId;

    public function __construct(string $errorId, string $message = "", int $code = 0, ?Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->errorId = $errorId;
    }

    public function getErrorId(): string {
        return $this->errorId;
    }

    public function isClientSafe(): bool {
        return true;
    }


}
