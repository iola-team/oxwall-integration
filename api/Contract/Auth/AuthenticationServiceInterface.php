<?php

namespace Iola\Api\Contract\Auth;

use Iola\Api\Auth\Identity;
use Zend\Authentication\AuthenticationServiceInterface as ZendAuthenticationServiceInterface;

interface AuthenticationServiceInterface
    extends ZendAuthenticationServiceInterface, AuthenticationAdatpterAwareInterface
{
    /**
     * @return Identity
     */
    public function getIdentity();
}