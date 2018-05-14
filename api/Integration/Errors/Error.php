<?php

namespace Everywhere\Api\Integration\Errors;

use Everywhere\Api\Contract\Integration\Errors\ErrorInterface;
use GraphQL\Error\UserError;

class Error extends UserError implements ErrorInterface
{

}
