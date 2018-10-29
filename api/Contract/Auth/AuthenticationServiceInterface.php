<?php
/**
 * Created by PhpStorm.
 * User: skambalin
 * Date: 28.10.17
 * Time: 16.26
 */

namespace Everywhere\Api\Contract\Auth;


use Everywhere\Api\Auth\Identity;
use Zend\Authentication\AuthenticationServiceInterface as ZendAuthenticationServiceInterface;

interface AuthenticationServiceInterface
    extends ZendAuthenticationServiceInterface, AuthenticationAdatpterAwareInterface
{
    /**
     * @return Identity
     */
    public function getIdentity();
}