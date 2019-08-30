<?php

namespace Iola\Api\Auth\Errors;

use Iola\Api\Contract\Auth\ErrorInterface;

abstract class AbstractError extends \Exception implements ErrorInterface
{
    protected $type;

    public function __construct($message, $type)
    {
        parent::__construct($message);

        $this->type = $type;
    }

    public function isClientSafe()
    {
        return true;
    }

    public function getCategory()
    {
        return "auth.{$this->type}";
    }
}
