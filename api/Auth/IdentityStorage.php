<?php

namespace Iola\Api\Auth;

use Iola\Api\Contract\Auth\IdentityStorageInterface;
use Zend\Authentication\Storage\NonPersistent;

class IdentityStorage extends NonPersistent implements IdentityStorageInterface
{

}