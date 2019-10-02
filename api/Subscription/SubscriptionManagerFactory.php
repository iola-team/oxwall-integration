<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

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
