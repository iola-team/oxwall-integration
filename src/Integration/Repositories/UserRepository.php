<?php
/**
 * Created by PhpStorm.
 * User: skambalin
 * Date: 19.10.17
 * Time: 17.38
 */

namespace Everywhere\Oxwall\Integration\Repositories;

use Everywhere\Api\Contract\Integration\UserRepositoryInterface;
use Everywhere\Api\Entities\Avatar;
use Everywhere\Api\Entities\Photo;
use Everywhere\Api\Entities\User;
use OW;

class UserRepository implements UserRepositoryInterface
{
    public $counter = 0;

    public function create($args) {
        $userDto = \BOL_UserService::getInstance()->createUser($args["name"], $args["password"], $args["login"]);

        $user = new User();
        $user->id = $userDto->id;
        $user->name = \BOL_UserService::getInstance()->getDisplayName($userDto->id);
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

        $userDtoList = \BOL_UserService::getInstance()->findUserListByIdList($idList);

        $users = [];

        /**
         * @var $userDto \BOL_User
         */
        foreach ($userDtoList as $userDto) {
            $user = new User();

            $user->id = $userDto->id;
            $user->name = \BOL_UserService::getInstance()->getDisplayName($userDto->id);
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

        $userIds = \BOL_UserService::getInstance()->findUserIdListByQuestionValues($searchFields, $args["offset"], $args["count"]);

        return $userIds;
    }

    public function countAll()
    {
        return \BOL_UserService::getInstance()->count(true);
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
        $items = \PHOTO_BOL_PhotoDao::getInstance()->findPhotoListByUserIdList($ids, $args["offset"], $args["count"]);
        $out = [];
        foreach ($items as $item) {
            $userId = (int) $item["userId"];
            $userItems = empty($out[$userId]) ? [] : $out[$userId];
            $userItems[] = (int) $item["id"];
            $out[$userId] = $userItems;
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
