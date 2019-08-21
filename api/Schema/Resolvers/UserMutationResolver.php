<?php

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Contract\Integration\UserRepositoryInterface;
use Iola\Api\Schema\CompositeResolver;

class UserMutationResolver extends CompositeResolver
{
    public function __construct(UserRepositoryInterface $userRepository)
    {
        parent::__construct([
            "deleteUser" => function($root, $args) use ($userRepository) {
                $userId = $args["id"]->getId();
                $userRepository->delete($userId);

                return ["deletedId" => $userId];
            }
        ]);
    }
}
