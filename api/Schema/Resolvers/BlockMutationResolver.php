<?php

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Auth\Errors\PermissionError;
use Iola\Api\Contract\Integration\BlockRepositoryInterface;
use Iola\Api\Schema\CompositeResolver;
use Iola\Api\Contract\Schema\ContextInterface;

class BlockMutationResolver extends CompositeResolver
{
    public function __construct(BlockRepositoryInterface $blockRepository)
    {
        parent::__construct();

        $this->addFieldResolver("blockUser", function($root, $args, ContextInterface $context) use($blockRepository) {
            $input = $args["input"];
            $userId = $input["userId"]->getId();
            $blockUserId = $input["blockUserId"]->getId();

            if ($userId !== $context->getViewer()->getUserId()) {
                throw new PermissionError();
            }

            $blockRepository->blockUser($userId, $blockUserId);

            return [
                "user" => $userId,
                "blockedUser" => $blockUserId
            ];
        });

        $this->addFieldResolver("unBlockUser", function($root, $args, ContextInterface $context) use($blockRepository) {
            $input = $args["input"];
            $userId = $input["userId"]->getId();
            $blockedUserId = $input["blockedUserId"]->getId();

            if ($userId !== $context->getViewer()->getUserId()) {
                throw new PermissionError();
            }

            $blockRepository->unBlockUser($userId, $blockedUserId);

            return [
                "user" => $userId,
                "unBlockedUser" => $blockedUserId
            ];
        });
    }
}