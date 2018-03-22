<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Schema\ContextInterface;
use Everywhere\Api\Contract\Schema\IDObjectInterface;
use Everywhere\Api\Contract\Schema\AbstractTypeResolverInterface;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use GraphQL\Utils;

class NodeResolver implements AbstractTypeResolverInterface
{
    /**
     * @param IDObjectInterface $root
     * @param ContextInterface $context
     * @param ResolveInfo $info
     *
     * @return Type
     */
    public function resolveType($root, ContextInterface $context, ResolveInfo $info)
    {
        if (!$root instanceof IDObjectInterface) {
            throw new InvariantViolation(
                'Node interface requires `IDObjectInterface` instance as root value, but received: ' . Utils::printSafe($root)
            );
        }

        return $info->schema->getType($root->getType());
    }
}
