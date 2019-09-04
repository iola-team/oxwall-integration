<?php

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Contract\Integration\UserRepositoryInterface;
use Iola\Api\Contract\Schema\ConnectionFactoryInterface;
use Iola\Api\Contract\Schema\ContextInterface;
use Iola\Api\Contract\Schema\DataLoaderFactoryInterface;
use Iola\Api\Contract\Schema\DataLoaderInterface;
use Iola\Api\Entities\User;
use Iola\Api\Schema\EntityResolver;
use GraphQL\Type\Definition\ResolveInfo;
use Iola\Api\Auth\Errors\PermissionError;
use Iola\Api\Contract\Integration\FriendshipRepositoryInterface;

class UserResolver extends EntityResolver
{
    /**
     * @var DataLoaderInterface
     */
    protected $isApprovedLoader;

    /**
     * @var DataLoaderInterface
     */
    protected $isEmailVerifiedLoader;

    /**
     * @var DataLoaderInterface
     */
    protected $photosLoader;

    /**
     * @var DataLoaderInterface
     */
    protected $photoCountsLoader;

    /**
     * @var DataLoaderInterface
     */
    protected $avatarLoader;

    /**
     * @var DataLoaderInterface
     */
    protected $chatLoader;

    /**
     * @var DataLoaderInterface
     */
    protected $chatsLoader;

    /**
     * @var DataLoaderInterface
     */
    protected $chatsCountsLoader;

    /**
     * @var DataLoaderInterface
     */
    protected $infoLoader;

    /**
     * @var ConnectionFactoryInterface
     */
    protected $connectionFactory;

    public function __construct(
        // Repositories
        UserRepositoryInterface $userRepository,
        FriendshipRepositoryInterface $friendshipRepository,

        // Factories
        DataLoaderFactoryInterface $loaderFactory,
        ConnectionFactoryInterface $connectionFactory
    ) {
        parent::__construct(
            $loaderFactory->create(function($ids, $args, $context) use($userRepository) {
                return $userRepository->findByIds($ids);
            })
        );

        $this->connectionFactory = $connectionFactory;

        $this->isOnlineLoader = $loaderFactory->create(function($ids, $args, $context) use($userRepository) {
            return $userRepository->getIsOnlineByIds($ids, $args);
        });

        $this->isApprovedLoader = $loaderFactory->create(function($ids, $args, $context) use($userRepository) {
            return $userRepository->getIsApprovedByIds($ids, $args);
        });

        $this->isEmailVerifiedLoader = $loaderFactory->create(function($ids, $args, $context) use($userRepository) {
            return $userRepository->getIsEmailVerifiedByIds($ids, $args);
        });

        $this->photosLoader = $loaderFactory->create(function($ids, $args, $context) use($userRepository) {
            return $userRepository->findPhotos($ids, $args);
        }, []);

        $this->photoCountsLoader = $loaderFactory->create(function($ids, $args, $context) use($userRepository) {
            return $userRepository->countPhotos($ids, $args);
        });

        $this->avatarLoader = $loaderFactory->create(function($ids, $args, $context) use($userRepository) {
            return $userRepository->findAvatars($ids, $args);
        });

        $this->infoLoader = $loaderFactory->create(function($ids, $args, $context) use($userRepository) {
            return $userRepository->getInfo($ids, $args);
        });

        $this->chatLoader = $loaderFactory->create(function($ids, $args, $context) use($userRepository) {
            return $userRepository->findChat($ids, $args);
        });
    }

    /**
     * @param User $user
     * @param $fieldName
     * @param $args
     * @param ContextInterface $context
     * @param $info
     *
     * @return mixed
     */
    protected function resolveField($user, $fieldName, $args, ContextInterface $context, ResolveInfo $info)
    {
        switch ($fieldName) {
            case "isOnline":
                return $this->isOnlineLoader->load($user->id, $args);

            case "isApproved":
                return $this->isApprovedLoader->load($user->id, $args);

            case "isEmailVerified":
                return $this->isEmailVerifiedLoader->load($user->id, $args);

            case "friends":
                return $this->connectionFactory->create($user, $args);

            case "comments":
                return $this->commentsLoader->load($user->id, $args);

            case "photos":
                return $this->connectionFactory->create(
                    $user,
                    $args,
                    function($args) use($user) {
                        return $this->photosLoader->load($user->id, $args);
                    },
                    function($args) use($user) {
                        return $this->photoCountsLoader->load($user->id, $args);
                    }
                );

            case "avatar":
                return $this->avatarLoader->load($user->id, $args);

            case "chat":
                if ((string) $user->id !== $context->getViewer()->getUserId()) {
                    throw new PermissionError();
                }

                return $this->chatLoader->load($user->id, [
                    "id" => isset($args["id"]) ? $args["id"]->getId() : null,
                    "recipientId" => isset($args["recipientId"]) ? $args["recipientId"]->getId() : null,
                ]);

            case "chats":
                if ((string) $user->id !== $context->getViewer()->getUserId()) {
                    throw new PermissionError();
                }

                return $this->connectionFactory->create($user, $args);

            /**
             * Pass user entity as root value to UserInfo and UserProfile resolvers
             */
            case "info":
            case "profile":
                return $user;

            default:
                return parent::resolveField($user, $fieldName, $args, $context, $info);
        }
    }
}
