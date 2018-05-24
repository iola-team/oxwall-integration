<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Integration\ProfileRepositoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderFactoryInterface;
use Everywhere\Api\Entities\ProfileField;
use Everywhere\Api\Schema\EntityResolver;

class ProfileFieldResolver extends EntityResolver
{
    public function __construct(ProfileRepositoryInterface $profileRepository, DataLoaderFactoryInterface $loaderFactory)
    {
        $entityLoader = $loaderFactory->create(function ($ids) use ($profileRepository) {
            return $profileRepository->findFieldsByIds($ids);
        });

        parent::__construct($entityLoader);

        $this->addFieldResolver('section', function(ProfileField $field) {
            return $field->sectionId;
        });

        $this->addFieldResolver('configs', function(ProfileField $field) {
            return [
                "_presentation_" => $field->presentation,
                "minDate" => new \DateTime(),
                "maxDate" => new \DateTime()
            ];
        });
    }
}
