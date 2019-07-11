<?php

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Contract\Integration\ProfileRepositoryInterface;
use Iola\Api\Contract\Schema\DataLoaderFactoryInterface;
use Iola\Api\Entities\User;
use Iola\Api\Schema\CompositeResolver;

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
