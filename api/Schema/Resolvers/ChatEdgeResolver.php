<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Auth\Errors\PermissionError;
use Iola\Api\Schema\CompositeResolver;
use Iola\Api\Contract\Schema\ConnectionFactoryInterface;
use Iola\Api\Contract\Schema\ContextInterface;
use Iola\Api\Contract\Schema\Relay\EdgeObjectInterface;

class ChatEdgeResolver extends CompositeResolver
{
    public function __construct(ConnectionFactoryInterface $connectionFactory)
    {
        parent::__construct();

        $this->addFieldResolver("unreadMessages", function(
            EdgeObjectInterface $edge, $arguments, ContextInterface $context
        ) use($connectionFactory) {
            $rootValue = $edge->getRootValue();
            $userId = isset($rootValue["userId"]) ? $rootValue["userId"] : null;
            
            if (!$userId || $userId !== $context->getViewer()->getUserId()) {
                throw new PermissionError();
            }

            $arguments = array_merge($arguments, [
                "filter" => [
                    "notReadBy" => $userId
                ]
            ]);

            return $edge->getNode()->then(function($node) use($connectionFactory, $arguments) {
                return $connectionFactory->create($node, $arguments);
            });
        });
    }
}
