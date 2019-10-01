<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Contract\Integration\ConfigRepositoryInterface;
use Iola\Api\Contract\Integration\UserRepositoryInterface;
use Iola\Api\Contract\Integration\ProfileRepositoryInterface;
use Iola\Api\Contract\Schema\ConnectionFactoryInterface;
use Iola\Api\Contract\Schema\ContextInterface;
use Iola\Api\Schema\CompositeResolver;
use Iola\Api\Contract\Schema\IDObjectInterface;

class QueryResolver extends CompositeResolver
{
    public function __construct(
        ConnectionFactoryInterface $connectionFactory,
        ConfigRepositoryInterface $configRepository,
        UserRepositoryInterface $userRepository,
        ProfileRepositoryInterface $profileRepository
    ) {
        parent::__construct();

        $this->addFieldResolver("config", function($root, $args) use($configRepository) {
            return $configRepository->getAll($args);
        });

        $this->addFieldResolver("me", function($root, $args, ContextInterface $context) {
            return $context->getViewer()->getUserId();
        });

        $this->addFieldResolver("users", function($root, $args) use($userRepository, $connectionFactory) {
            $filter = & $args["filter"];
            $filter["ids"] = empty($filter["ids"])
                ? null
                /**
                 * TODO: Figure out how to stop converting id objects each time they should be used in args
                 */
                : array_map(function(IDObjectInterface $idObject) {
                    return $idObject->getId();
                }, $filter["ids"]);

            return $connectionFactory->create(
                $root,
                $args,
                function($args) use($userRepository) {
                    return $userRepository->findAllIds($args);
                },
                function($args) use($userRepository) {
                    return $userRepository->countAll($args);
                }
            );
        });

        $this->addFieldResolver("accountTypes", function($root, $args) use($profileRepository) {
            return $profileRepository->findAccountTypeIds();
        });

        $this->addFieldResolver("node", function($root, $args) use($userRepository) {
            return $args["id"];
        });
    }
}
