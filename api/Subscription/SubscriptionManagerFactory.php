<?php

namespace Everywhere\Api\Subscription;

use Everywhere\Api\Contract\App\EventManagerInterface;
use Everywhere\Api\Contract\Schema\ContextInterface;
use Everywhere\Api\Contract\Subscription\SubscriptionManagerFactoryInterface;
use GraphQL\Server\ServerConfig;

class SubscriptionManagerFactory implements SubscriptionManagerFactoryInterface
{
    /**
     * @var ServerConfig
     */
    protected $serverConfig;

    public function __construct(ServerConfig $serverConfig)
    {
        $this->serverConfig = $serverConfig;
    }

    public function create(EventManagerInterface $eventManager)
    {
        return new SubscriptionManager($eventManager, $this->serverConfig);
    }
}
