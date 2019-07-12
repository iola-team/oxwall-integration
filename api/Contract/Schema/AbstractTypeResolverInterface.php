<?php

namespace Iola\Api\Contract\Schema;

use GraphQL\Type\Definition\ResolveInfo;

interface AbstractTypeResolverInterface
{
    public function resolveType($root, ContextInterface $context, ResolveInfo $info);
}
