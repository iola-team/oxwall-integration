<?php
namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Schema\CompositeResolver;
use Everywhere\Api\Contract\Schema\ConnectionFactoryInterface;
use Everywhere\Api\Contract\Schema\Relay\EdgeObjectInterface;

class ChatEdgeResolver extends CompositeResolver
{
    public function __construct(ConnectionFactoryInterface $connectionFactory)
    {
        parent::__construct();

        $this->addFieldResolver("unreadMessages", function(EdgeObjectInterface $edge, $arguments) use($connectionFactory) {
            $rootValue = $edge->getRootValue();
            
            if (isset($rootValue["userId"])) {
                $arguments = array_merge($arguments, [
                    "filter" => [
                        "notReadBy" => $rootValue["userId"]
                    ]
                ]);
            }

            return $edge->getNode()->then(function($node) use($connectionFactory, $arguments) {
                return $connectionFactory->create($node, $arguments);
            });
        });
    }
}
