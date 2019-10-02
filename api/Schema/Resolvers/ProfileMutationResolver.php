<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Contract\Integration\ProfileRepositoryInterface;
use Iola\Api\Contract\Schema\ContextInterface;
use Iola\Api\Contract\Schema\DataLoaderFactoryInterface;
use Iola\Api\Contract\Schema\DataLoaderInterface;
use Iola\Api\Contract\Schema\IDFactoryInterface;
use Iola\Api\Entities\ProfileField;
use Iola\Api\Schema\CompositeResolver;
use Iola\Api\Schema\IDObject;
use GraphQL\Error\InvariantViolation;
use GraphQL\Executor\Values;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Iola\Api\Auth\Errors\PermissionError;

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

        if ($userId !== $context->getViewer()->getUserId()) {
            throw new PermissionError();
        }

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

            $valueIds = $this->profileRepository->saveFieldValues($userId, $data);

            return [
                "user" => $userId,
                "nodes" => $valueIds
            ];
        });
    }
}
