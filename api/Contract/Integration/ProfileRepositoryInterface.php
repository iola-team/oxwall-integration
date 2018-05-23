<?php

namespace Everywhere\Api\Contract\Integration;

use Everywhere\Api\Entities\AccountType;
use Everywhere\Api\Entities\ProfileField;
use Everywhere\Api\Entities\ProfileFieldSection;
use Everywhere\Api\Entities\ProfileFieldValue;

interface ProfileRepositoryInterface
{
    const PLACE_SIGN_UP = 'SIGN_UP';
    const PLACE_VIEW = 'VIEW';
    const PLACE_EDIT = 'EDIT';

    /**
     * @return mixed[]
     */
    public function findAccountTypeIds();

    /**
     * @param array $ids
     * @return AccountType[]
     */
    public function findAccountTypesByIds(array $ids);

    /**
     * @param array $ids
     * @param array $args
     * @return mixed[]
     */
    public function findFieldIdsByAccountTypeIds(array $ids, array $args);

    /**
     * @param array $ids
     * @return ProfileField[]
     */
    public function findFieldsByIds(array $ids);

    /**
     * @param array $ids
     * @return ProfileFieldSection[]
     */
    public function findFieldSectionsByIds(array $ids);

    /**
     * @param string[] $ids
     * @return ProfileFieldValue[]
     */
    public function findFieldValuesByIds($ids);

    /**
     * @param string[] $userIds
     * @param string[] $fieldIds
     * @return string[]
     */
    public function findFieldValuesIds(array $userIds, array $fieldIds);

    /**
     * @param $userId
     * @param array $values
     * @return null
     */
    public function saveUserFieldValues($userId, array $values);
}
