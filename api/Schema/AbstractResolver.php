<?php

namespace Everywhere\Api\Schema;

use Everywhere\Api\Contract\Schema\ObjectTypeResolverInterface;
use GraphQL\Utils\Utils;

abstract class AbstractResolver implements ObjectTypeResolverInterface
{
    protected function undefined() {
        return Utils::undefined();
    }
}
