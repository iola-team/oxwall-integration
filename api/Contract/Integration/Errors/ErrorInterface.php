<?php
namespace Everywhere\Api\Contract\Integration\Errors;

use GraphQL\Error\ClientAware;

interface ErrorInterface extends \Throwable, ClientAware
{

}
