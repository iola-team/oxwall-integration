<?php

namespace Everywhere\Api\Schema;

use Everywhere\Api\Contract\Schema\ContextInterface;
use GraphQL\Type\Definition\ResolveInfo;

class CompositeResolver extends AbstractResolver
{
    protected $resolvers = [];

    public function __construct(array $resolvers = [])
    {
        foreach ($resolvers as $fieldName => $resolver) {
            $this->addFieldResolver($fieldName, $resolver);
        }
    }

    protected function addFieldResolver($fieldName, callable $resolver)
    {
        $this->resolvers[$fieldName] = $resolver;
    }

    protected function resolveField($root, $fieldName, $args, ContextInterface $context, ResolveInfo $info) {
        if (empty($this->resolvers[$fieldName])) {
            return $this->undefined();
        }

        return call_user_func($this->resolvers[$fieldName], $root, $args, $context, $info);
    }

    public function resolve($root, $args, ContextInterface $context, ResolveInfo $info)
    {
       return $this->resolveField($root, $info->fieldName, $args, $context, $info);
    }
}
