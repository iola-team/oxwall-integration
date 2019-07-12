<?php

namespace Iola\Api\Integration\Errors;

use Iola\Api\Contract\Integration\Errors\ErrorInterface;
use GraphQL\Error\UserError;

class Error extends UserError implements ErrorInterface
{

}
