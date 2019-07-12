<?php

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Contract\Integration\ProfileRepositoryInterface;
use Iola\Api\Contract\Schema\DataLoaderFactoryInterface;
use Iola\Api\Entities\AccountType;
use Iola\Api\Schema\EntityResolver;

class AccountTypeResolver extends EntityResolver
{
    public function __construct(ProfileRepositoryInterface $profileRepository, DataLoaderFactoryInterface $loaderFactory)
    {
        $entityLoader = $loaderFactory->create(function ($ids) use ($profileRepository) {
            return $profileRepository->findAccountTypesByIds($ids);
        });

        $fieldsLoader = $loaderFactory->create(function ($ids, $args) use ($profileRepository) {
            return $profileRepository->findFieldIdsByAccountTypeIds($ids, $args);
        });

        parent::__construct($entityLoader);

        $this->addFieldResolver("fields", function(AccountType $accountType, $args) use ($fieldsLoader) {
           return $fieldsLoader->load($accountType->getId(), $args);
        });
    }
}
