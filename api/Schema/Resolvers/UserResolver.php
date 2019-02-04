<?php
/**
 * Created by PhpStorm.
 * User: skambalin
 * Date: 19.10.17
 * Time: 15.16
 */

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Integration\UserRepositoryInterface;
use Everywhere\Api\Contract\Schema\ConnectionFactoryInterface;
use Everywhere\Api\Contract\Schema\ContextInterface;
use Everywhere\Api\Contract\Schema\DataLoaderFactoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderInterface;
use Everywhere\Api\Entities\User;
use Everywhere\Api\Schema\EntityResolver;
use Everywhere\Api\Schema\IDObject;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Type\Definition\ResolveInfo;
use Everywhere\Api\Contract\Integration\FriendshipRepositoryInterface;
use Everywhere\Api\Entities\Friendship;
use Everywhere\Api\Contract\Schema\IDObjectInterface;

class UserResolver extends EntityResolver
{
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

        $this->chatsLoader = $loaderFactory->create(function($ids, $args, $context) use($userRepository) {
            return $userRepository->findChats($ids, $args);
        });

        $this->chatsCountsLoader = $loaderFactory->create(function($ids, $args, $context) use($userRepository) {
            return $userRepository->countChats($ids, $args);
        });
    }

    /**
     * TODO: Get rid of this ugly conversion somehow. 
     * Perhaps it would be better to do such convertion on type resolving phase, 
     * since we usually do not need id types in resolver functions.
     * The only exception is `node` resolver.
     * 
     * @param IDObjectInterface[] $idObjects
     * @return string[]
     */
    protected function convertIdObjectsToLocalIds($idObjects)
    {
        return array_map(function(IDObjectInterface $idObject) {
            return $idObject->getId();
        }, $idObjects);
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
                return $this->chatLoader->load($user->id, [
                    "id" => isset($args["id"]) ? $args["id"]->getId() : null,
                    "recipientId" => isset($args["recipientId"]) ? $args["recipientId"]->getId() : null,
                ]);

            case "chats":
                return $this->connectionFactory->create(
                    $user,
                    $args,
                    function($args) use($user) {
                        return $this->chatsLoader->load($user->id, $args);
                    },
                    function($args) use($user) {
                        return $this->chatsCountsLoader->load($user->id, $args);
                    }
                );

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
