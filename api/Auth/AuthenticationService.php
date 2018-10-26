<?php

namespace Everywhere\Api\Auth;

use Everywhere\Api\Contract\Auth\AuthenticationServiceInterface;
use Zend\Authentication\AuthenticationService as ZendAuthenticationService;

class AuthenticationService extends ZendAuthenticationService implements AuthenticationServiceInterface
{

}