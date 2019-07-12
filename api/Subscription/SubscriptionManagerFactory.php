<?php

namespace Iola\Api\Subscription;

use Iola\Api\Contract\App\EventManagerInterface;
use Iola\Api\Contract\Schema\ContextInterface;
use Iola\Api\Contract\Subscription\SubscriptionManagerFactoryInterface;
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
