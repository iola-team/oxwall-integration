<?php

namespace Everywhere\Api\Subscription;

use Everywhere\Api\Contract\App\EventManagerInterface;
use Everywhere\Api\Contract\Schema\ContextInterface;
use Everywhere\Api\Contract\Subscription\SubscriptionManagerFactoryInterface;
use Everywhere\Api\Contract\Subscription\SubscriptionManagerInterface;
use GraphQL\Executor\Promise\PromiseAdapter;
use GraphQL\Type\Schema;

class SubscriptionManagerFactory implements SubscriptionManagerFactoryInterface
{
    protected $schema;
    protected $context;
    protected $promiseAdapter;

    public function __construct(
        Schema $schema,
        ContextInterface $context,
        PromiseAdapter $promiseAdapter
    )
    {
        $this->schema = $schema;
        $this->context = $context;
        $this->promiseAdapter = $promiseAdapter;
    }

    public function create(EventManagerInterface $eventManager)
    {
        return new SubscriptionManager(
            $eventManager,
            $this->schema,
            $this->context,
            $this->promiseAdapter
        );
    }
}
