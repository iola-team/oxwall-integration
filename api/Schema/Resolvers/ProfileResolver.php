<?php
/**
 * Created by PhpStorm.
 * User: skambalin
 * Date: 19.10.17
 * Time: 15.16
 */

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Integration\ProfileRepositoryInterface;
use Everywhere\Api\Contract\Integration\UserRepositoryInterface;
use Everywhere\Api\Contract\Schema\ConnectionFactoryInterface;
use Everywhere\Api\Contract\Schema\ContextInterface;
use Everywhere\Api\Contract\Schema\DataLoaderFactoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderInterface;
use Everywhere\Api\Entities\ProfileField;
use Everywhere\Api\Entities\User;
use Everywhere\Api\Schema\CompositeResolver;
use GraphQL\Type\Definition\ResolveInfo;

class ProfileResolver extends CompositeResolver
{
    public function __construct(
        ProfileRepositoryInterface $profileRepository,
        DataLoaderFactoryInterface $loaderFactory
    ) {
        $fieldsLoader = $loaderFactory->create(function($ids, $args) use ($profileRepository) {
            return $profileRepository->findFieldIdsByAccountTypeIds($ids, $args);
        });

        $valuesLoader = $loaderFactory->create(function($ids, $args) use ($profileRepository) {
            return $profileRepository->findFieldValuesIds($ids, $args["fieldIds"]);
        });

        parent::__construct([
            "accountType" => function(User $user) {
                return $user->accountTypeId;
            },
            "values" => function(User $user) use ($fieldsLoader, $valuesLoader) {
                return $fieldsLoader->load($user->accountTypeId)->then(function($fieldIds) use($user, $valuesLoader) {
                    return $valuesLoader->load($user->getId(), [
                        "fieldIds" => $fieldIds
                    ]);
                });
            }
        ]);
    }
}
