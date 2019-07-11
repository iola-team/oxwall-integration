<?php

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Contract\Integration\ProfileRepositoryInterface;
use Iola\Api\Contract\Schema\DataLoaderFactoryInterface;
use Iola\Api\Schema\EntityResolver;

class ProfileFieldSectionResolver extends EntityResolver
{
    public function __construct(ProfileRepositoryInterface $profileRepository, DataLoaderFactoryInterface $loaderFactory)
    {
        $entityLoader = $loaderFactory->create(function ($ids) use ($profileRepository) {
            return $profileRepository->findFieldSectionsByIds($ids);
        });

        parent::__construct($entityLoader);
    }
}
