<?php
/**
 * Created by PhpStorm.
 * User: skambalin
 * Date: 28.10.17
 * Time: 17.11
 */

namespace Everywhere\Api\Contract\Auth;


use Everywhere\Api\Auth\Identity;
use Zend\Authentication\Storage\StorageInterface;

interface IdentityStorageInterface extends StorageInterface
{
    /**
     * @return Identity
     */
    public function read();
}