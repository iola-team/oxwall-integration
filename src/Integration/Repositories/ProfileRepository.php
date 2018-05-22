<?php

namespace Everywhere\Oxwall\Integration\Repositories;

use Everywhere\Api\Contract\Integration\ProfileRepositoryInterface;
use Everywhere\Api\Entities\AccountType;
use Everywhere\Api\Entities\ProfileField;
use Everywhere\Api\Entities\ProfileFieldSection;

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
            "text" => ProfileField::PRESENTATION_TEXT,
            "textarea" => ProfileField::PRESENTATION_TEXTAREA,
            "password" => ProfileField::PRESENTATION_PASSWORD,
            "birthdate" => ProfileField::PRESENTATION_DATE,
            "date" =>  ProfileField::PRESENTATION_DATE,
            "url" => ProfileField::PRESENTATION_URL,
            "multicheckbox" => ProfileField::PRESENTATION_MULTI_CHOICE,
            "fselect" => ProfileField::PRESENTATION_MULTI_CHOICE,
            "radio" => ProfileField::PRESENTATION_SINGLE_CHOICE,
            "select" => ProfileField::PRESENTATION_SINGLE_CHOICE,
            "checkbox" => ProfileField::PRESENTATION_SWITCH
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

    public function getFieldValuesByUserIds(array $userIds, array $fieldIds)
    {
        return $this->questionService->getQuestionData($userIds, $fieldIds);
    }

    public function saveUserFieldValues($userId, array $values)
    {
        return $this->questionService->saveQuestionsData($values, $userId);
    }
}
