<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Integration\ProfileRepositoryInterface;
use Everywhere\Api\Schema\CompositeResolver;
use Everywhere\Api\Schema\IDObject;

class ProfileMutationResolver extends CompositeResolver
{
    /**
     * @var ProfileRepositoryInterface 
     */
    protected $profileRepository;

    public function __construct(ProfileRepositoryInterface $profileRepository)
    {
        $this->profileRepository = $profileRepository;

        parent::__construct([
            "saveProfileFieldValues" => [$this, "saveFieldValue"]
        ]);
    }

    protected function getFinalValue($fieldValue)
    {
        return $fieldValue["value"];
    }

    public function saveFieldValue($root, $args)
    {
        /**
         * @var $userIdObject IDObject
         */
        $userIdObject = $args["input"]["userId"];
        $userId = $userIdObject->getId();
        $values = $args["input"]["values"];

        $data = [];
        foreach ($values as $value) {
            $fieldName = $value["fieldName"];
            $data[$fieldName] = $this->getFinalValue($value);
        }

        $this->profileRepository->saveUserFieldValues($userId, $data);

        return [
            "user" => $userId
        ];
    }
}
