<?php
/**
 * Created by PhpStorm.
 * User: skambalin
 * Date: 19.10.17
 * Time: 15.16
 */

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Integration\UsersRepositoryInterface;
use Everywhere\Api\Contract\Schema\ContextInterface;
use Everywhere\Api\Contract\Schema\DataLoaderFactoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderInterface;
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
    protected $photosLoader;

    /**
     * @var DataLoaderInterface
     */
    protected $avatarLoader;

    public function __construct(UsersRepositoryInterface $usersRepository, DataLoaderFactoryInterface $loaderFactory) {
        parent::__construct(
            $loaderFactory->create(function($ids, $args, $context) use($usersRepository) {
                return $usersRepository->findByIds($ids);
            })
        );

        $this->friendListLoader = $loaderFactory->create(function($ids, $args, $context) use($usersRepository) {
            return $usersRepository->findFriends($ids, $args);
        }, []);

        $this->photosLoader = $loaderFactory->create(function($ids, $args, $context) use($usersRepository) {
            return $usersRepository->findPhotos($ids, $args);
        }, []);

        $this->avatarLoader = $loaderFactory->create(function($ids, $args, $context) use($usersRepository) {
            return $usersRepository->findAvatars($ids, $args);
        }, null);
    }

    /**
     * @param $user
     * @param $fieldName
     * @param $args
     * @param ContextInterface $context
     * @param $info
     *
     * @return Promise|null
     */
    protected function resolveField($user, $fieldName, $args, ContextInterface $context, ResolveInfo $info)
    {
        switch ($fieldName) {
            case "friends":
                return $this->friendListLoader->load($user->id, $args);

            case "comments":
                return $this->commentsLoader->load($user->id, $args);

            case "photos":
                return $this->photosLoader->load($user->id, $args);

            case "avatar":
                return $this->avatarLoader->load($user->id, $args);

            default:
                return parent::resolveField($user, $fieldName, $args, $context, $info);
        }
    }
}
