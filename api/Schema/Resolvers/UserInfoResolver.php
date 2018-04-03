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
use Everywhere\Api\Entities\User;
use Everywhere\Api\Schema\CompositeResolver;
use GraphQL\Type\Definition\ResolveInfo;

class UserInfoResolver extends CompositeResolver
{
    /**
     * @var DataLoaderInterface
     */
    protected $infoLoader;

    public function __construct(UsersRepositoryInterface $usersRepository, DataLoaderFactoryInterface $loaderFactory) {
        parent::__construct();

        $this->infoLoader = $loaderFactory->create(function($ids, $args, $context) use($usersRepository) {
            return $usersRepository->getInfo($ids, $args);
        });
    }

    /**
     * @param User $root
     * @param $fieldName
     * @param $args
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @return mixed
     */
    protected function resolveField($root, $fieldName, $args, ContextInterface $context, ResolveInfo $info)
    {
        return $this->infoLoader->load($root->getId(), [
            "name" => $fieldName
        ]);
    }
}
