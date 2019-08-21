<?php

namespace Iola\Oxwall\Repositories;

use Iola\Api\Contract\Integration\UserRepositoryInterface;
use Iola\Api\Entities\Avatar;
use Iola\Api\Entities\User;
use OW;

class UserRepository implements UserRepositoryInterface
{
    public $counter = 0;

    /**
     * @var \MAILBOX_BOL_ConversationService
     */
    protected $conversationService;

    /**
     * @var \BOL_UserService
     */
    protected $userService;

    public function __construct()
    {
        $this->conversationService = \MAILBOX_BOL_ConversationService::getInstance();
        $this->userService = \BOL_UserService::getInstance();
    }

    public function convertDisplayName($displayName, $postfix = 0)
    {
        $displayName = preg_replace("/-/", "_", \URLify::filter($displayName));
        $result = $displayName . (empty($postfix) ? "" : $postfix);

        if ($this->userService->isExistUserName($result)) {
            $postfix++;

            return $this->convertDisplayName($displayName, $postfix);
        }

        return $result;
    }

    public function create($args)
    {
        $username = $this->convertDisplayName($args["name"]);
        $displayNameField = OW::getConfig()->getValue("base", "display_name_question");
        $questionsData = [$displayNameField => $args["name"]];

        $userDto = $this->userService->createUser($username, $args["password"], $args["email"]);
        \BOL_QuestionService::getInstance()->saveQuestionsData($questionsData, $userDto->id);

        $user = new User();
        $user->id = $userDto->id;
        $user->name = $this->userService->getDisplayName($userDto->id);
        $user->email = $userDto->email;
        $user->activityTime = (int) $userDto->activityStamp;
        $user->isEmailVerified = $userDto->emailVerify;

        $event = new \OW_Event(\OW_EventManager::ON_USER_REGISTER, [
            "method" => "iola",
            "userId" => $user->id,
            "params" => $args
        ]);
        OW::getEventManager()->trigger($event);

        return $user;
    }

    public function trackUserActivity($userId)
    {
        $this->userService->updateActivityStamp($userId, \BOL_UserService::USER_CONTEXT_MOBILE);
    }

    public function authenticate($identity, $password)
    {
        $result = \OW_Auth::getInstance()->authenticate(
            new \BASE_CLASS_StandardAuth($identity, $password)
        );

        if (!$result->isValid()) {
            return null;
        }

        return $result->getUserId();
    }

    public function sendResetPasswordInstructions($input)
    {
        $errorCode = null;
        $language = OW::getLanguage();

        try {
            $this->userService->processResetForm($input);
        } catch (\LogicException $error) {
            switch ($error->getMessage()) {
                case $language->text("base", "forgot_password_no_user_error_message"):
                    $errorCode = "NOT_FOUND";
                    break;
                case $language->text("base", "forgot_password_request_exists_error_message"):
                    $errorCode = "DUPLICATE";
                    break;
                default:
                    $errorCode = "COMMON";
                    break;
            }
        } catch (\Exception $error) {
            // Possible mail send error
            $errorCode = "COMMON";
        }

        return $errorCode;
    }

    public function sendEmailVerificationInstructions($input)
    {
        $errorCode = null;

        try {
            /**
             * @var $userDto \BOL_User
             */
            $userDto = $this->userService->findByEmail($input["email"]);

            if ($userDto) {
                if (!$userDto->emailVerify) {
                    \BOL_EmailVerifyService::getInstance()->sendUserVerificationMail($userDto);
                }
            } else {
                $errorCode = "NOT_FOUND";
            }
        } catch (\Exception $error) {
            // Possible mail send error
            $errorCode = "COMMON";
        }

        return $errorCode;
    }

    public function findByIds($idList)
    {
        $this->counter++;

        $usersDto = $this->userService->findUserListByIdList($idList);
        $users = [];

        /**
         * @var $userDto \BOL_User
         */
        foreach ($usersDto as $userDto) {
            $user = new User($userDto->id);

            $user->name = $this->userService->getDisplayName($userDto->id);
            $user->accountTypeId = $userDto->accountType;
            $user->email = $userDto->email;
            $user->activityTime = (int) $userDto->activityStamp;

            $users[$userDto->id] = $user;
        }

        return $users;
    }

    public function findAllIds(array $args)
    {
        $searchFields = [];

        if (!empty($args["filter"]["ids"])) {
            return $this->userService->findUserIdListByIdList($args["filter"]["ids"]);
        }

        if (!empty($args["filter"]["search"])) {
            $displayNameField = OW::getConfig()->getValue("base", "display_name_question");
            $searchFields[$displayNameField] = $args["filter"]["search"];
        }

        if (!empty($args["filter"]["email"])) {
            $searchFields["email"] = $args["filter"]["email"];
        }

        if (!empty($searchFields)) {
            return $this->userService->findUserIdListByQuestionValues($searchFields, $args["offset"], $args["count"]);
        }

        $idMapper = function ($user) {
            return $user->id;
        };

        // TODO: Refactor the method to use ListType enum ("online", "featured", etc...) instead of separate flags
        if (!empty($args["filter"]["online"])) {
            $onlineList = $this->userService->findOnlineList($args["offset"], $args["count"]);

            return array_map($idMapper, $onlineList);
        }

        if (!empty($args["filter"]["featured"])) {
            $featuredUsers = $this->userService->findFeaturedList($args["offset"], $args["count"]);

            return array_map($idMapper, $featuredUsers);
        }

        return $this->userService->findLatestUserIdsList($args["offset"], $args["count"]);
    }

    public function countAll(array $args)
    {
        $searchFields = [];

        if (!empty($args["filter"]["ids"])) {
            $existingUserIds = $this->userService->findUserIdListByIdList($args["filter"]["ids"]);

            return count($existingUserIds);
        }

        if (!empty($args["filter"]["search"])) {
            $displayNameField = OW::getConfig()->getValue("base", "display_name_question");
            $searchFields[$displayNameField] = $args["filter"]["search"];
        }

        if (!empty($args["filter"]["email"])) {
            $searchFields["email"] = $args["filter"]["email"];
        }

        if (!empty($searchFields)) {
            return $this->userService->countUsersByQuestionValues($searchFields);
        }

        // TODO: Refactor the method to use ListType enum ("online", "featured", etc...) instead of separate flags
        if (!empty($args["filter"]["featured"])) {
            return $this->userService->countFeatured();
        }

        if (!empty($args["filter"]["online"])) {
            return $this->userService->countOnline();
        }

        return $this->userService->count();
    }

    public function getIsOnlineByIds($ids) {
        return $this->userService->findOnlineStatusForUserList($ids);
    }

    public function getIsApprovedByIds($ids) {
        $out = [];
        $userApproveDao = \BOL_UserApproveDao::getInstance();
        $unapprovedUserIds = $userApproveDao->findUnapproveStatusForUserList($ids);

        foreach($ids as $userId) {
            $out[$userId] = !in_array($userId, $unapprovedUserIds);
        }

        return $out;
    }

    public function getIsEmailVerifiedByIds($ids) {
        $out = [];

        // FYI: $example is used to bypass the Oxwall cache (userService->findUserListByIdList)
        $example = new \OW_Example();
        $example->andFieldInArray("id", $ids);
        $usersDto = \BOL_UserDao::getInstance()->findListByExample($example);

        /**
         * @var $userDto \BOL_User
         */
        foreach ($usersDto as $userDto) {
            $out[$userDto->id] = $userDto->emailVerify;
        }

        return $out;
    }

    public function findPhotos($ids, array $args)
    {
        $out = [];
        foreach ($ids as $userId) {
            $items = \PHOTO_BOL_PhotoDao::getInstance()->findPhotoListByUserId($userId, $args["offset"], $args["count"]);
            foreach ($items as $item) {
                $userItems = empty($out[$userId]) ? [] : $out[$userId];
                $userItems[] = (int) $item["id"];
                $out[$userId] = $userItems;
            }
        }

        return $out;
    }

    public function countPhotos($ids, array $args)
    {
        $out = [];

        foreach ($ids as $id) {
            $out[$id] = \PHOTO_BOL_PhotoService::getInstance()->countUserPhotos($id);
        }

        return $out;
    }

    public function findAvatars($ids, array $args)
    {
        $avatarService = \BOL_AvatarService::getInstance();
        $avatars = $avatarService->findByUserIdList($ids);
        $out = [];

        /**
         * @var $avatar \BOL_Avatar
         */
        foreach ($avatars as $avatar) {
            $out[$avatar->userId] = $avatar->id;
        }

        return $out;
    }

    public function findChat($ids, array $args)
    {
        $conversationDto = null;

        if (!empty($args["id"])) {
            $conversationDto = $this->conversationService->getConversation($args["id"]);
        }

        $out = [];
        foreach ($ids as $userId) {
            $out[$userId] = null;

            if (!$conversationDto && !empty($args["recipientId"])) {
                $recipientId = $args["recipientId"];
                $conversations = $this->conversationService->findConversationList($userId, $recipientId);
                $conversations = empty($conversations)
                    ? $this->conversationService->findConversationList($recipientId, $userId)
                    : $conversations;

                $conversationDto = reset($conversations) ?: null;
            }

            if (
                $conversationDto
                && ($conversationDto->initiatorId == $userId || $conversationDto->interlocutorId == $userId )
            ) {
                $out[$userId] = $conversationDto->id;
            }
        }

        return $out;
    }

    public function getInfo($ids, array $args)
    {
        $mapInfoToQuestion = [
            "headline" => "email",
            "about" => "field_aff1910847312babd2834f91eee934fe",
            "location" => "326fcde5fad55adb56e57044418f8b5d"
        ];

        $questionName = $mapInfoToQuestion[$args["name"]];
        $data = \BOL_QuestionService::getInstance()->getQuestionData($ids, [$questionName]);

        $out = [];
        foreach ($data as $userId => $values) {
            $out[$userId] = empty($values[$questionName]) ? null : $values[$questionName];
        }

        return $out;
    }

    public function delete($userId)
    {
        $this->userService->deleteUser($userId, true);

        return ["deletedId" => $userId];
    }
}
