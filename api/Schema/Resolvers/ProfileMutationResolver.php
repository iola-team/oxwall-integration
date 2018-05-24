<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Integration\ProfileRepositoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderFactoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderInterface;
use Everywhere\Api\Contract\Schema\IDFactoryInterface;
use Everywhere\Api\Entities\ProfileField;
use Everywhere\Api\Schema\CompositeResolver;
use Everywhere\Api\Schema\IDObject;
use GraphQL\Error\InvariantViolation;

class ProfileMutationResolver extends CompositeResolver
{
    use ProfileFiledValuePropertiesTrait;

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

    protected function getFinalValue(ProfileField $field, $fieldValue)
    {
        $valuePropNames = $this->getAllowedValueProperties($field->presentation);
        $value = $this->undefined();

        foreach ($valuePropNames as $propName) {
            if (isset($fieldValue[$propName])) {
                $value = $fieldValue[$propName];
            }
        }

        if ($value === $this->undefined()) {
            throw new InvariantViolation(
                "`$field->presentation` fields support only `"
                . implode(", ", $valuePropNames)
                . "` data properties"
            );
        }

        return $value;
    }

    public function saveFieldValue($root, $args)
    {
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

        return $this->fieldLoader->loadMany($fieldIds)->then(function($fields) use($values, $userId) {
            $data = [];
            foreach ($values as $index => $value) {
                /**
                 * @var $field ProfileField
                 */
                $field = $fields[$index];
                $data[$field->getId()] = $this->getFinalValue($field, $value);
            }

            $this->profileRepository->saveFieldValues($userId, $data);

            return [
                "user" => $userId
            ];
        });
    }
}
