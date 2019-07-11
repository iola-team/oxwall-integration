<?php

namespace Iola\Api\Auth;

use Iola\Api\Contract\Auth\AuthenticationServiceInterface;
use Zend\Authentication\AuthenticationService as ZendAuthenticationService;

class AuthenticationService extends ZendAuthenticationService implements AuthenticationServiceInterface
{

}