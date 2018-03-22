<?php

namespace Everywhere\Api\Contract\Schema;

use GraphQL\Type\Definition\ResolveInfo;

interface ObjectTypeResolverInterface
{
    public function resolve($root, $args, ContextInterface $context, ResolveInfo $info);
}
