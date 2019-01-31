<?php

namespace Everywhere\Oxwall\Integration\Repositories;

use Everywhere\Api\Contract\Integration\UserRepositoryInterface;
use Everywhere\Api\Entities\Avatar;
use Everywhere\Api\Entities\Photo;
use Everywhere\Api\Entities\User;
use OW;

class UserRepository implements UserRepositoryInterface
{
    public $counter = 0;

    /**
     * @var \MAILBOX_BOL_ConversationService
     */
    protected $conversationService;

    /**
     * @var \MAILBOX_BOL_ConversationDao
     */
    protected $conversationDao;

    /**
     * @var \BOL_UserService
     */
    protected $userService;

    public function __construct()
    {
        $this->conversationService = \MAILBOX_BOL_ConversationService::getInstance();
        $this->conversationDao = \MAILBOX_BOL_ConversationDao::getInstance();
        $this->userService = \BOL_UserService::getInstance();
    }

    public function convertDisplayName($displayName, $postfix = 0)
    {
        $displayName = preg_replace('/-/', '_', \URLify::filter($displayName));
        $result = $displayName . (empty($postfix) ? '' : $postfix);

        if ($this->userService->isExistUserName($result)) {
            $postfix++;

            return $this->convertDisplayName($displayName, $postfix);
        }

        return $result;
    }

    public function create($args)
    {
        $displayNameValue = $this->convertDisplayName($args["name"]);
        $displayNameField = OW::getConfig()->getValue("base", "display_name_question");
        $questionsData = [$displayNameField => $displayNameValue];

        $userDto = $this->userService->createUser($displayNameValue, $args["password"], $args["email"]);
        \BOL_QuestionService::getInstance()->saveQuestionsData($questionsData, $userDto->id);

        $user = new User();
        $user->id = $userDto->id;
        $user->name = $this->userService->getDisplayName($userDto->id);
        $user->email = $userDto->email;
        $user->activityTime = (int) $userDto->activityStamp;

        return $user;
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

    public function findByIds($idList)
    {
        $this->counter++;

        $userDtoList = $this->userService->findUserListByIdList($idList);

        $users = [];

        /**
         * @var $userDto \BOL_User
         */
        foreach ($userDtoList as $userDto) {
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

        if (isset($args["search"])) {
            $displayNameField = OW::getConfig()->getValue('base', 'display_name_question');
            $searchFields[$displayNameField] = $args["search"];
        }

        if (isset($args["email"])) {
            $searchFields["email"] = $args["email"];
        }

        if (isset($args["featured"])) {
            $featuredUsers = $this->userService->findFeaturedList($args["offset"], $args["count"]);
            $userIds = array_column($featuredUsers, 'id');

            return $userIds;
        }

        $userIds = $this->userService->findUserIdListByQuestionValues($searchFields, $args["offset"], $args["count"]);

        return $userIds;
    }

    public function countAll(array $args)
    {
        if (isset($args["email"])) {
            return $this->userService->isExistEmail($args["email"]) ? 1 : 0;
        }

        return $this->userService->count(true);
    }

    public function findFeaturedIds(array $args)
    {
        return $this->userService->findFeaturedList($args["offset"], $args["count"]);
    }

    public function countFeatured()
    {
        return $this->userService->countFeatured();
    }

    public function findFriends($userIds, array $args)
    {
        $this->counter++;
        $out = [];
        foreach ($userIds as $userId) {
            $out[$userId] =  \FRIENDS_BOL_Service::getInstance()->findFriendIdList($userId, $args["offset"], $args["count"]);
        }

        return $out;
    }

    public function countFriends($ids, array $args)
    {
        $out = [];
        foreach ($ids as $id) {
            $out[$id] = \FRIENDS_BOL_Service::getInstance()->countFriends($id);
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

        if (isset($args["id"])) {
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

    public function findChats($ids, array $args)
    {
        $activeModes = $this->conversationService->getActiveModeList();

        $out = [];
        foreach ($ids as $id) {
            $out[$id] = [];
            $conversationInfoList = $this->conversationDao->findConversationItemListByUserId($id, $activeModes, $args["offset"], $args["count"]);
            foreach ($conversationInfoList as $conversationInfo) {
                $out[$id][] = $conversationInfo["id"];
            }
        }

        return $out;
    }

    public function countChats($ids, array $args)
    {
        $out = [];
        foreach ($ids as $id) {
            $out[$id] = $this->conversationService->countConversationListByUserId($id);
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

    public function __destruct()
    {
        $this->counter;
    }
}
