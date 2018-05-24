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
    }

    /**
     * @param ProfileFieldValue $root
     * @param string $fieldName
     * @param $args
     * @param ContextInterface $context
     * @param ResolveInfo $info
     *
     * @return mixed
     * @throws
     */
    public function resolveField($root, $fieldName, $args, ContextInterface $context, ResolveInfo $info)
    {
        $field = $info->parentType->getField($fieldName);
        $presentationDirective = $info->schema->getDirective("presentation");
        $directiveValue = Values::getDirectiveValues($presentationDirective, $field->astNode);

        if (empty($directiveValue["list"])) {
            return parent::resolveField($root, $fieldName, $args, $context, $info);
        }

        $presentations = $directiveValue["list"];

        return $this->fieldLoader->load($root->fieldId)->then(function(ProfileField $field) use ($fieldName, $root, $presentations) {
            return in_array($field->presentation, $presentations) ? $root->value : null;
        });
    }
}
