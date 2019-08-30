<?php

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Auth\Errors\PermissionError;
use Iola\Api\Contract\Integration\UserRepositoryInterface;
use Iola\Api\Contract\Schema\ContextInterface;
use Iola\Api\Schema\CompositeResolver;

class UserMutationResolver extends CompositeResolver
{
    public function __construct(UserRepositoryInterface $userRepository)
    {
        parent::__construct([
            "deleteUser" => function($root, $args, ContextInterface $context) use ($userRepository) {
                $userId = $args["id"]->getId();

                if ($userId !== $context->getViewer()->getUserId()) {
                    throw new PermissionError();
                }

                $userRepository->delete($userId);

                return ["deletedId" => $userId];
            }
        ]);
    }
}
