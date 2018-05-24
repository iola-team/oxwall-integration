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
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Utils\Utils;

class ProfileFieldValueResolver extends EntityResolver
{
    use ProfileFiledValuePropertiesTrait;

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
    }

    public function resolveValue(ProfileField $field, $valueName, $value)
    {
        $propNames = $this->getAllowedValueProperties($field->presentation);

        return in_array($valueName, $propNames) ? $value : null;
    }

    /**
     * @param ProfileFieldValue $root
     * @param string $fieldName
     * @param $args
     * @param ContextInterface $context
     * @param ResolveInfo $info
     *
     * @return mixed
     */
    public function resolveField($root, $fieldName, $args, ContextInterface $context, ResolveInfo $info)
    {
        if (array_key_exists($fieldName, $this->valueProps)) {
            return $this->fieldLoader->load($root->fieldId)->then(function($field) use ($fieldName, $root) {
                return $this->resolveValue($field, $fieldName, $root->value);
            });
        }

        return parent::resolveField($root, $fieldName, $args, $context, $info);
    }
}
