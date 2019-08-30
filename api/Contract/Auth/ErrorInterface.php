<?php

namespace Iola\Api\Contract\Auth;

use GraphQL\Error\ClientAware;

/**
 * TODO: Extend from `Throwable` interface after dropping php 5.6 support
 */
interface ErrorInterface extends ClientAware
{

}