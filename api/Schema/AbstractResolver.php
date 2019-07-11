<?php

namespace Iola\Api\Schema;

use Iola\Api\Contract\Schema\ObjectTypeResolverInterface;
use GraphQL\Utils\Utils;

abstract class AbstractResolver implements ObjectTypeResolverInterface
{
    protected function undefined() {
        return Utils::undefined();
    }
}
