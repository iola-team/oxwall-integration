<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Integration\ProfileRepositoryInterface;
use Everywhere\Api\Contract\Schema\ContextInterface;
use Everywhere\Api\Contract\Schema\DataLoaderFactoryInterface;
use Everywhere\Api\Contract\Schema\IDObjectInterface;
use Everywhere\Api\Entities\ProfileField;
use Everywhere\Api\Entities\ProfileFieldValue;
use Everywhere\Api\Schema\CompositeResolver;
use Everywhere\Api\Schema\EntityResolver;
use GraphQL\Error\InvariantViolation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Utils\Utils;

class ProfileFieldValueResolver extends EntityResolver
{
    use ProfileFiledValueSanitizeTrait;

    protected $validators = [

    ];

    public function __construct(ProfileRepositoryInterface $profileRepository, DataLoaderFactoryInterface $loaderFactory) {
        $entityLoader = $loaderFactory->create(function($ids) use ($profileRepository) {
            return $profileRepository->findFieldValuesByIds($ids);
        });

        $fieldLoader = $loaderFactory->create(function($ids) use ($profileRepository) {
            return $profileRepository->findFieldsByIds($ids);
        });

        parent::__construct($entityLoader);

        $this->addFieldResolver("field", function(ProfileFieldValue $value) use ($fieldLoader) {
            return $fieldLoader->load($value->fieldId);
        });

        $this->addFieldResolver("value", function(ProfileFieldValue $value) use ($fieldLoader) {
            return $fieldLoader->load($value->fieldId)->then(function($field) use ($value) {
                return $this->sanitizeOutputValue($field, $value->value);
            });
        });
    }
}
