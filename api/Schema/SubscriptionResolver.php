<?php

namespace Everywhere\Api\Schema;

use Everywhere\Api\Contract\Schema\ContextInterface;
use Everywhere\Api\Contract\Schema\SubscriptionInterface;
use GraphQL\Type\Definition\ResolveInfo;

class SubscriptionResolver extends CompositeResolver
{
    public function resolve($root, $args, ContextInterface $context, ResolveInfo $info)
    {
        /**
         * @var $subscription SubscriptionInterface
         */
        $subscription = parent::resolve($root, $args, $context, $info);

        // TODO: implement

        return $subscription;
    }
}
