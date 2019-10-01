<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Oxwall\Repositories;

use Iola\Api\Contract\Integration\ProfileRepositoryInterface;
use Iola\Api\Entities\AccountType;
use Iola\Api\Entities\ProfileField;
use Iola\Api\Entities\ProfileFieldSection;
use Iola\Api\Entities\ProfileFieldValue;

class ProfileRepository implements ProfileRepositoryInterface
{
    /**
     * @var \BOL_QuestionService
     */
    protected $questionService;

    public function __construct()
    {
        $this->questionService = \BOL_QuestionService::getInstance();
    }

    public function findAccountTypesByIds(array $ids)
    {
        $accountTypes = [];
        foreach ($ids as $id) {
            /**
             * @var $accountTypeDto \BOL_QuestionAccountType
             */
            $accountTypeDto = $this->questionService->findAccountTypeByName($id);
            $accountType = new AccountType($accountTypeDto->name);
            $accountType->label = $this->questionService->getAccountTypeLang($accountTypeDto->name);

            $accountTypes[$id] = $accountType;
        }

        return $accountTypes;
    }

    public function findAccountTypeIds()
    {
        $accountTypeDtos = $this->questionService->findAllAccountTypes();
        $ids = [];

        /**
         * @var $accountTypeDto \BOL_QuestionAccountType
         */
        foreach ($accountTypeDtos as $accountTypeDto) {
            $ids[] = $accountTypeDto->name;
        }

        return $ids;
    }

    public function findFieldIdsByAccountTypeIds(array $ids, array $args)
    {
        $out = [];
        $place = empty($args["on"]) ? null : $args["on"];
        $places = [
            self::PLACE_SIGN_UP => "findSignUpQuestionsForAccountType",
            self::PLACE_EDIT => "findEditQuestionsForAccountType",
            self::PLACE_VIEW => "findViewQuestionsForAccountType",
        ];

        foreach ($ids as $id) {
            $out[$id] = [];

            $questionDtos = empty($places[$place])
                ? $this->questionService->findAllQuestionsForAccountType($id)
                : call_user_func([$this->questionService, $places[$place]], $id);

            /**
             * @var $questionDto \BOL_Question
             */
            foreach ($questionDtos as $questionDto) {
                $out[$id][] = $questionDto["name"];
            }
        }

        return $out;
    }

    protected function getPresentation($presentation)
    {
        $aliasing = [
            \BOL_QuestionService::QUESTION_PRESENTATION_TEXT => ProfileField::PRESENTATION_TEXT,
            \BOL_QuestionService::QUESTION_PRESENTATION_TEXTAREA => ProfileField::PRESENTATION_TEXT,
            \BOL_QuestionService::QUESTION_PRESENTATION_PASSWORD => ProfileField::PRESENTATION_TEXT,
            \BOL_QuestionService::QUESTION_PRESENTATION_BIRTHDATE => ProfileField::PRESENTATION_DATE,
            \BOL_QuestionService::QUESTION_PRESENTATION_DATE =>  ProfileField::PRESENTATION_DATE,
            \BOL_QuestionService::QUESTION_PRESENTATION_AGE => ProfileField::PRESENTATION_DATE,
            \BOL_QuestionService::QUESTION_PRESENTATION_URL => ProfileField::PRESENTATION_TEXT,
            \BOL_QuestionService::QUESTION_PRESENTATION_MULTICHECKBOX => ProfileField::PRESENTATION_SELECT,
            \BOL_QuestionService::QUESTION_PRESENTATION_FSELECT => ProfileField::PRESENTATION_SELECT,
            \BOL_QuestionService::QUESTION_PRESENTATION_RADIO => ProfileField::PRESENTATION_SELECT,
            \BOL_QuestionService::QUESTION_PRESENTATION_SELECT => ProfileField::PRESENTATION_SELECT,
            \BOL_QuestionService::QUESTION_PRESENTATION_CHECKBOX => ProfileField::PRESENTATION_SWITCH,
            \BOL_QuestionService::QUESTION_PRESENTATION_RANGE => ProfileField::PRESENTATION_RANGE
        ];

        return $aliasing[$presentation];
    }

    /**
     * @param \BOL_Question $questionDto
     * @param \BOL_QuestionValue[] $values
     *
     * @return array
     */
    protected function getFieldConfigs(\BOL_Question $questionDto, $values)
    {
        $questionConfigs = empty($questionDto->custom) ? [] : json_decode($questionDto->custom, true);

        if ($questionDto->name === "email") {
            return [
                "format" => ProfileField::TEXT_FORMAT_EMAIL,
            ];
        }

        switch ($questionDto->presentation) {
            case \BOL_QuestionService::QUESTION_PRESENTATION_BIRTHDATE:
            case \BOL_QuestionService::QUESTION_PRESENTATION_DATE:
            case \BOL_QuestionService::QUESTION_PRESENTATION_AGE:
                $currentYear = (int) date("Y");
                $minYear = isset($questionConfigs["year_range"]["from"])
                    ? $questionConfigs["year_range"]["from"]
                    : $currentYear;

                $maxYear = isset($questionConfigs["year_range"]["to"])
                    ? $questionConfigs["year_range"]["to"]
                    : $currentYear - 100;

                return [
                    "minDate" => (new \DateTime())->setDate($minYear, 1, 1),
                    "maxDate" => (new \DateTime())->setDate($maxYear, 12, 31)
                ];

            case \BOL_QuestionService::QUESTION_PRESENTATION_URL:
                return [
                    "format" => ProfileField::TEXT_FORMAT_URL,
                ];

            case \BOL_QuestionService::QUESTION_PRESENTATION_PASSWORD:
                return [
                    "secure" => true,
                    "minLength" => \UTIL_Validator::PASSWORD_MIN_LENGTH,
                    "maxLength" => \UTIL_Validator::PASSWORD_MAX_LENGTH,
                ];

            case \BOL_QuestionService::QUESTION_PRESENTATION_TEXTAREA:
                return [
                    "multiline" => true,
                ];

            case \BOL_QuestionService::QUESTION_PRESENTATION_MULTICHECKBOX:
                $multiple = true;
            case \BOL_QuestionService::QUESTION_PRESENTATION_SELECT:
            case \BOL_QuestionService::QUESTION_PRESENTATION_RADIO:
            case \BOL_QuestionService::QUESTION_PRESENTATION_FSELECT:
                $options = [];
                foreach ($values as $value) {
                    $options[] = [
                        "label" => $this->questionService->getQuestionValueLang($value->questionName, $value->value),
                        "value" => $value->value
                    ];
                }

                return [
                    "multiple" => !empty($multiple),
                    "options" => $options
                ];
        }

        return $questionConfigs;
    }

    public function findFieldsByIds(array $ids)
    {
        $out = [];
        $questionDtos = $this->questionService->findQuestionByNameList($ids);
        $questionValues = $this->questionService->findQuestionsValuesByQuestionNameList($ids);

        /**
         * @var $questionDto \BOL_Question
         */
        foreach ($questionDtos as $questionDto) {
            $configs = $questionDto->custom;
            $questionValue = empty($questionValues[$questionDto->name])
                ? [] :
                $questionValues[$questionDto->name]["values"];

            $profileField = new ProfileField($questionDto->name);
            $profileField->name = $questionDto->name;
            $profileField->label = $this->questionService->getQuestionLang($questionDto->name);
            $profileField->presentation = $this->getPresentation($questionDto->presentation);
            $profileField->isRequired = !empty($questionDto->required);
            $profileField->sectionId = $questionDto->sectionName;
            $profileField->configs = $this->getFieldConfigs($questionDto, $questionValue);

            $out[$questionDto->name] = $profileField;
        }

        return $out;
    }

    public function findFieldSectionsByIds(array $ids)
    {
        $out = [];
        $sectionDtos = $this->questionService->findSectionBySectionNameList($ids);

        /**
         * @var $sectionDto \BOL_QuestionSection
         */
        foreach ($sectionDtos as $sectionDto) {
            $section = new ProfileFieldSection($sectionDto->name);
            $section->label = $this->questionService->getSectionLang($sectionDto->name);

            $out[$sectionDto->name] = $section;
        }

        return $out;
    }

    /**
     * Fetches profile field values by list of virtual ids ("[\"fieldId\", \"userId\"]")
     *
     * @param string[] $ids
     * @return ProfileFieldValue[]
     */
    public function findFieldValuesByIds($ids)
    {
        $parsedIds = array_map("json_decode", $ids);
        $userIds = array_unique(array_map("array_pop", $parsedIds));
        $fields = array_unique(array_map("array_shift", $parsedIds));

        $questions = $this->questionService->findQuestionByNameList($fields);
        $questionValues = $this->questionService->findQuestionsValuesByQuestionNameList($fields);
        $data = $this->questionService->getQuestionData($userIds, $fields);
        $out = [];

        foreach ($ids as $index => $id) {
            list($fieldId, $userId) = $parsedIds[$index];

            $value = empty($data[$userId][$fieldId])
                ? null :
                $this->extractQuestionValue(
                    $questions[$fieldId],
                    $data[$userId][$fieldId],
                    empty($questionValues[$fieldId]) ? [] : $questionValues[$fieldId]["values"]
                );

            $fieldValue = new ProfileFieldValue($id);
            $fieldValue->fieldId = $fieldId;
            $fieldValue->value = $value;

            $out[$id] = $fieldValue;
        }

        return $out;
    }

    protected function extractQuestionValue(\BOL_Question $question, $value, $questionValues)
    {
        if ($question->name === "joinStamp") {
            return new \DateTime("@" . $value);
        }

        switch ($question->type) {
            case \BOL_QuestionService::QUESTION_VALUE_TYPE_SELECT:
            case \BOL_QuestionService::QUESTION_VALUE_TYPE_FSELECT:
                return [$value];

            case \BOL_QuestionService::QUESTION_VALUE_TYPE_MULTISELECT:
                $out = [];

                /**
                 * @var $valueDto \BOL_QuestionValue
                 */
                foreach ($questionValues as $valueDto) {
                    if (intval($value) & intval($valueDto->value)) {
                        $out[] = (string) $valueDto->value;
                    }
                }

                return $out;

            case \BOL_QuestionService::QUESTION_VALUE_TYPE_DATETIME:
                return new \DateTime($value);
        }

        return $value;
    }

    protected function convertInputValue(\BOL_Question $question, $value)
    {
        switch ($question->type) {
            case \BOL_QuestionService::QUESTION_VALUE_TYPE_SELECT:
            case \BOL_QuestionService::QUESTION_VALUE_TYPE_FSELECT:
            case \BOL_QuestionService::QUESTION_VALUE_TYPE_MULTISELECT:
                return array_sum($value);

            case \BOL_QuestionService::QUESTION_VALUE_TYPE_DATETIME:
                /* @var $value \DateTime */
                return $value->format("Y-m-d H:i:s");

            case \BOL_QuestionService::QUESTION_VALUE_TYPE_BOOLEAN:
                return $value ? '1' : '0';
        }

        return $value;
    }

    /**
     * Oxwall do not have special entities for profile field values
     * We will generated virtual ids ("[\"fieldId\", \"userId\"]") for them
     *
     * @param string $userId
     * @param string $fieldId
     * @return string
     */
    protected function generateFieldValueId($userId, $fieldId)
    {
        return json_encode([(string) $fieldId, (string) $userId]);
    }

    public function findFieldValuesIds(array $userIds, array $fieldIds)
    {
        $out = [];
        foreach ($userIds as $userId) {
            $out[$userId] = [];
            foreach ($fieldIds as $id) {
                $out[$userId][] = $this->generateFieldValueId($userId, $id);
            }
        }

        return $out;
    }

    public function saveFieldValues($userId, array $values)
    {
        $fieldIds = array_keys($values);
        $questionDtos = $this->questionService->findQuestionByNameList($fieldIds);

        $out = [];
        $dataToSave = [];
        foreach ($questionDtos as $questionDto) {
            $dataToSave[$questionDto->name] = $this->convertInputValue($questionDto, $values[$questionDto->name]);
            $out[] = $this->generateFieldValueId($userId, $questionDto->name);
        }

        $this->questionService->saveQuestionsData($dataToSave, $userId);

        return $out;
    }
}
