<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Schema\SubscriptionFactoryInterface;
use Everywhere\Api\Schema\SubscriptionResolver;

class NewMessageSubscriptionResolver extends SubscriptionResolver
{
    public function __construct(SubscriptionFactoryInterface $subscriptionFactory)
    {
        parent::__construct();

        $this->addFieldResolver("onMessageAdd", function() use ($subscriptionFactory) {
            return $subscriptionFactory->create('messages.new');
        });
    }
}
