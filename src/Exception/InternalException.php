<?php

namespace Graphette\Graphette\Exception;

use GraphQL\Error\ClientAware;

class InternalException extends \Exception implements ClientAware {

    public function isClientSafe(): bool {
        return false;
    }

}
