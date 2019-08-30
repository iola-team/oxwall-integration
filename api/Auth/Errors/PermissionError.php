<?php

namespace Iola\Api\Auth\Errors;

use Iola\Api\Contract\Auth\ErrorInterface;

class PermissionError extends AbstractError implements ErrorInterface
{
    public function __construct($message = null)
    {
        parent::__construct(
            $message ?: "Insufficient permission",
            "permission"
        );
    }
}
