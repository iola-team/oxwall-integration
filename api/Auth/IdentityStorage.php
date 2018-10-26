<?php

namespace Everywhere\Api\Auth;

use Everywhere\Api\Contract\Auth\IdentityStorageInterface;
use Zend\Authentication\Storage\NonPersistent;

class IdentityStorage extends NonPersistent implements IdentityStorageInterface
{

}