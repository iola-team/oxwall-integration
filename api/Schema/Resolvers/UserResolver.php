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
use GraphQL\Executor\Promise\Promise;
use GraphQL\Type\Definition\ResolveInfo;

class UserResolver extends EntityResolver
{
    /**
     * @var DataLoaderInterface
     */
    protected $friendListLoader;

    /**
     * @var DataLoaderInterface
     */
    protected $friendCountsLoader;

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
    protected $chatCountsLoader;

    /**
     * @var DataLoaderInterface
     */
    protected $infoLoader;

    /**
     * @var ConnectionFactoryInterface
     */
    protected $connectionFactory;

    public function __construct(
        UserRepositoryInterface $userRepository,
        DataLoaderFactoryInterface $loaderFactory,
        ConnectionFactoryInterface $connectionFactory
    ) {
        parent::__construct(
            $loaderFactory->create(function($ids, $args, $context) use($userRepository) {
                return $userRepository->findByIds($ids);
            })
        );

        $this->connectionFactory = $connectionFactory;
        $this->friendListLoader = $loaderFactory->create(function($ids, $args, $context) use($userRepository) {
            return $userRepository->findFriends($ids, $args);
        }, []);

        $this->friendCountsLoader = $loaderFactory->create(function($ids, $args, $context) use($userRepository) {
            return $userRepository->countFriends($ids, $args);
        }, []);

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
            return $userRepository->findChats($ids, $args);
        });

        $this->chatCountsLoader = $loaderFactory->create(function($ids, $args, $context) use($userRepository) {
            return $userRepository->countChats($ids, $args);
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
            case "friends":
                return $this->connectionFactory->create(
                    $user,
                    $args,
                    function($args) use($user) {
                        return $this->friendListLoader->load($user->id, $args);
                    },
                    function($args) use($user) {
                        return $this->friendCountsLoader->load($user->id, $args);
                    }
                 );

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

            case "chats":
                return $this->connectionFactory->create(
                    $user,
                    $args,
                    function($args) use($user) {
                        return $this->chatLoader->load($user->id, $args);
                    },
                    function($args) use($user) {
                        return $this->chatCountsLoader->load($user->id, $args);
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
