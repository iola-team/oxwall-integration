<?php

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Contract\Integration\ProfileRepositoryInterface;
use Iola\Api\Contract\Schema\DataLoaderFactoryInterface;
use Iola\Api\Contract\Schema\DataLoaderInterface;
use Iola\Api\Entities\ProfileField;
use Iola\Api\Entities\ProfileFieldValue;
use Iola\Api\Schema\EntityResolver;

class ProfileFieldValueResolver extends EntityResolver
{
    /**
     * @var DataLoaderInterface
     */
    protected $fieldLoader;

    public function __construct(ProfileRepositoryInterface $profileRepository, DataLoaderFactoryInterface $loaderFactory) {
        $entityLoader = $loaderFactory->create(function($ids) use ($profileRepository) {
            return $profileRepository->findFieldValuesByIds($ids);
        });

        parent::__construct($entityLoader);

        $this->fieldLoader = $loaderFactory->create(function($ids) use ($profileRepository) {
            return $profileRepository->findFieldsByIds($ids);
        });

        $this->addFieldResolver("field", function(ProfileFieldValue $value) {
            return $this->fieldLoader->load($value->fieldId);
        });

        $this->addFieldResolver("data", function(ProfileFieldValue $value) {
            if (!$value || $value->value === null) {
                return null;
            }

            return $this->fieldLoader->load($value->fieldId)->then(function(ProfileField $field) use ($value) {
                return [
                    "presentation" => $field->presentation,
                    "value" => $value->value
                ];
            });
        });
    }
}
