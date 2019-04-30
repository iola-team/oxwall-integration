<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Integration\ProfileRepositoryInterface;
use Everywhere\Api\Contract\Schema\ContextInterface;
use Everywhere\Api\Contract\Schema\DataLoaderFactoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderInterface;
use Everywhere\Api\Contract\Schema\IDObjectInterface;
use Everywhere\Api\Entities\ProfileField;
use Everywhere\Api\Entities\ProfileFieldValue;
use Everywhere\Api\Schema\CompositeResolver;
use Everywhere\Api\Schema\EntityResolver;
use GraphQL\Error\InvariantViolation;
use GraphQL\Executor\Values;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Utils\Utils;

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
