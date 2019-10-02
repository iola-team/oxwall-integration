<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

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
