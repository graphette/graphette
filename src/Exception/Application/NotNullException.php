<?php

namespace Graphette\Graphette\Exception\Application;

use Graphette\Graphette\Exception\ApplicationException;

class NotNullException extends ApplicationException
{

    public function __construct(string $message = "Non-nullable", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('NOT_NULL', $message, $code, $previous);
    }

}
