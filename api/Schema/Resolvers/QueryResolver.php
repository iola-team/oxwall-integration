<?php
/**
 * Created by PhpStorm.
 * User: skambalin
 * Date: 19.10.17
 * Time: 15.16
 */

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Integration\ConfigRepositoryInterface;
use Everywhere\Api\Contract\Integration\UserRepositoryInterface;
use Everywhere\Api\Contract\Integration\ProfileRepositoryInterface;
use Everywhere\Api\Contract\Schema\ConnectionFactoryInterface;
use Everywhere\Api\Contract\Schema\ContextInterface;
use Everywhere\Api\Contract\Schema\ObjectResolverInterface;
use Everywhere\Api\Contract\Schema\ResolverInterface;
use Everywhere\Api\Entities\User;
use Everywhere\Api\Schema\CompositeResolver;
use Everywhere\Api\Schema\ConnectionObject;
use Everywhere\Api\Schema\ConnectionResult;
use GraphQL\Type\Definition\ResolveInfo;
use Everywhere\Api\Contract\Schema\IDObjectInterface;

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
