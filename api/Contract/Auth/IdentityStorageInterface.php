<?php

namespace Iola\Api\Contract\Auth;

use Iola\Api\Auth\Identity;
use Zend\Authentication\Storage\StorageInterface;

interface IdentityStorageInterface extends StorageInterface
{
    /**
     * @return Identity
     */
    public function read();
}