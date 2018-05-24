<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Integration\ProfileRepositoryInterface;
use Everywhere\Api\Contract\Schema\ContextInterface;
use Everywhere\Api\Contract\Schema\DataLoaderFactoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderInterface;
use Everywhere\Api\Contract\Schema\IDFactoryInterface;
use Everywhere\Api\Entities\ProfileField;
use Everywhere\Api\Schema\CompositeResolver;
use Everywhere\Api\Schema\IDObject;
use GraphQL\Error\InvariantViolation;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Values;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;

class ProfileMutationResolver extends CompositeResolver
{
    /**
     * @var ProfileRepositoryInterface 
     */
    protected $profileRepository;

    /**
     * @var DataLoaderInterface
     */
    protected $fieldLoader;

    /**
     * @var IDFactoryInterface
     */
    protected $idFactory;

    public function __construct(
        ProfileRepositoryInterface $profileRepository,
        DataLoaderFactoryInterface $loaderFactory,
        IDFactoryInterface $idFactory
    ) {
        parent::__construct([
            "saveProfileFieldValues" => [$this, "saveFieldValue"]
        ]);

        $this->idFactory = $idFactory;
        $this->profileRepository = $profileRepository;
        $this->fieldLoader = $loaderFactory->create(function($ids) {
            return $this->profileRepository->findFieldsByIds($ids);
        });
    }

    protected function getFinalValue(ProfileField $field, $allowedPresentations, $fieldValue)
    {
        $value = $this->undefined();
        $allowed = [];

        foreach ($allowedPresentations as $propName => $presentations) {
            if (in_array($field->presentation, $presentations)) {
                $value = empty($fieldValue[$propName]) ? $value : $fieldValue[$propName];
                $allowed[] = $propName;
            }
        }

        if ($value === $this->undefined()) {
            throw new InvariantViolation(
                "`$field->presentation` fields support only `"
                . implode(", ", $allowed)
                . "` data properties"
            );
        }

        return $value;
    }

    /**
     * Extracts allowed presentation for each data prop based on @presentation schema directive
     *
     * @param ResolveInfo $info
     * @return array
     * @throws \Exception
     */
    protected function getPresentationsMap(ResolveInfo $info)
    {
        $presentationDirective = $info->schema->getDirective("presentation");
        $inputType = Type::getNamedType(
            $info->parentType->getField($info->fieldName)->getArg("input")->getType()
        );
        $valueType = Type::getNamedType($inputType->getField("values")->getType());

        $allowedPresentations = [];
        foreach ($valueType->getFields() as $valueField) {
            $directiveValue = Values::getDirectiveValues($presentationDirective, $valueField->astNode);

            if (!empty($directiveValue["list"])) {
                $allowedPresentations[$valueField->name] = $directiveValue["list"];
            }
        }

        return $allowedPresentations;
    }

    public function saveFieldValue($root, $args, ContextInterface $context, ResolveInfo $info)
    {
        $allowedPresentations = $this->getPresentationsMap($info);

        /**
         * @var $userIdObject IDObject
         */
        $userIdObject = $args["input"]["userId"];
        $userId = $userIdObject->getId();
        $values = $args["input"]["values"];

        $fieldIds = [];

        foreach ($values as $value) {
            $fieldIds[] = $this->idFactory->createFromGlobalId($value["fieldId"])->getId();
        }

        return $this->fieldLoader->loadMany($fieldIds)->then(function($fields) use($values, $userId, $allowedPresentations) {
            $data = [];
            foreach ($values as $index => $value) {
                /**
                 * @var $field ProfileField
                 */
                $field = $fields[$index];
                $data[$field->getId()] = $this->getFinalValue($field, $allowedPresentations, $value);
            }

            $this->profileRepository->saveFieldValues($userId, $data);

            return [
                "user" => $userId
            ];
        });
    }
}
