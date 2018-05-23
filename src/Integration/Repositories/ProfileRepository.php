<?php

namespace Everywhere\Oxwall\Integration\Repositories;

use Everywhere\Api\Contract\Integration\ProfileRepositoryInterface;
use Everywhere\Api\Entities\AccountType;
use Everywhere\Api\Entities\ProfileField;
use Everywhere\Api\Entities\ProfileFieldSection;
use Everywhere\Api\Entities\ProfileFieldValue;

class ProfileRepository implements ProfileRepositoryInterface
{
    /**
     * @var \BOL_QuestionService
     */
    private $questionService;

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

    private function getPresentation($presentation)
    {
        $aliasing = [
            \BOL_QuestionService::QUESTION_PRESENTATION_TEXT => ProfileField::PRESENTATION_TEXT,
            \BOL_QuestionService::QUESTION_PRESENTATION_TEXTAREA => ProfileField::PRESENTATION_TEXTAREA,
            \BOL_QuestionService::QUESTION_PRESENTATION_PASSWORD => ProfileField::PRESENTATION_PASSWORD,
            \BOL_QuestionService::QUESTION_PRESENTATION_BIRTHDATE => ProfileField::PRESENTATION_DATE,
            \BOL_QuestionService::QUESTION_PRESENTATION_DATE =>  ProfileField::PRESENTATION_DATE,
            \BOL_QuestionService::QUESTION_PRESENTATION_URL => ProfileField::PRESENTATION_URL,
            \BOL_QuestionService::QUESTION_PRESENTATION_MULTICHECKBOX => ProfileField::PRESENTATION_MULTI_CHOICE,
            \BOL_QuestionService::QUESTION_PRESENTATION_FSELECT => ProfileField::PRESENTATION_MULTI_CHOICE,
            \BOL_QuestionService::QUESTION_PRESENTATION_RADIO => ProfileField::PRESENTATION_SINGLE_CHOICE,
            \BOL_QuestionService::QUESTION_PRESENTATION_SELECT => ProfileField::PRESENTATION_SINGLE_CHOICE,
            \BOL_QuestionService::QUESTION_PRESENTATION_AGE => ProfileField::PRESENTATION_SINGLE_CHOICE,
            \BOL_QuestionService::QUESTION_PRESENTATION_CHECKBOX => ProfileField::PRESENTATION_SWITCH,
            \BOL_QuestionService::QUESTION_PRESENTATION_RANGE => ProfileField::PRESENTATION_RANGE
        ];

        return $aliasing[$presentation];
    }

    public function findFieldsByIds(array $ids)
    {
        $out = [];
        $questionDtos = $this->questionService->findQuestionByNameList($ids);

        /**
         * @var $questionDto \BOL_Question
         */
        foreach ($questionDtos as $questionDto) {
            $profileField = new ProfileField($questionDto->name);
            $profileField->name = $questionDto->name;
            $profileField->label = $this->questionService->getQuestionLang($questionDto->name);
            $profileField->presentation = $this->getPresentation($questionDto->presentation);
            $profileField->dataType = $questionDto->type;
            $profileField->isRequired = !empty($questionDto->required);
            $profileField->sectionId = $questionDto->sectionName;

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
                $this->convertQuestionValue(
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

    private function convertQuestionValue(\BOL_Question $question, $value, $questionValues)
    {
        $type = $question->type;

        if ($question->name === "joinStamp") {
            return new \DateTime("@" . $value);
        }

        switch ($type) {
            case \BOL_QuestionService::QUESTION_VALUE_TYPE_SELECT:
            case \BOL_QuestionService::QUESTION_VALUE_TYPE_FSELECT:
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

    /**
     * Oxwall do not have special entities for profile field values
     * We will generated virtual ids ("[\"fieldId\", \"userId\"]") for them
     *
     * @param string[] $userIds
     * @param string[] $fieldIds
     * @return string[]
     */
    public function findFieldValuesIds(array $userIds, array $fieldIds)
    {
        $out = [];
        foreach ($userIds as $userId) {
            $out[$userId] = [];
            foreach ($fieldIds as $id) {
                $out[$userId][] = json_encode([$id, $userId]);
            }
        }

        return $out;
    }

    public function saveUserFieldValues($userId, array $values)
    {
        return $this->questionService->saveQuestionsData($values, $userId);
    }
}
