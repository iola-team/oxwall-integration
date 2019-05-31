<?php
/**
 * Created by PhpStorm.
 * User: skambalin
 * Date: 19.10.17
 * Time: 17.37
 */

namespace Everywhere\Oxwall\Integration;

use Everywhere\Api\Contract\App\EventManagerInterface;
use Everywhere\Api\Contract\Integration\IntegrationInterface;
use Everywhere\Api\Contract\Schema\ViewerInterface;

use Everywhere\Api\Integration\Events\UserUpdateEvent;
use Everywhere\Api\Integration\Events\MessageAddedEvent;
use Everywhere\Api\Integration\Events\MessageUpdatedEvent;
use Everywhere\Api\Integration\Events\CommentAddedEvent;
use Everywhere\Api\Integration\Events\FriendshipUpdatedEvent;

use Everywhere\Oxwall\Integration\Repositories\ConfigRepository;
use Everywhere\Oxwall\Integration\Repositories\AvatarRepository;
use Everywhere\Oxwall\Integration\Repositories\ChatRepository;
use Everywhere\Oxwall\Integration\Repositories\ProfileRepository;
use Everywhere\Oxwall\Integration\Repositories\SubscriptionRepository;
use Everywhere\Oxwall\Integration\Repositories\UserRepository;
use Everywhere\Oxwall\Integration\Repositories\PhotoRepository;
use Everywhere\Oxwall\Integration\Repositories\CommentRepository;
use Everywhere\Oxwall\Integration\Repositories\FriendshipRepository;

use OW;
use OW_Event;
use Everywhere\Api\Integration\Events\FriendshipAddedEvent;
use Everywhere\Api\Integration\Events\FriendshipDeletedEvent;

class Integration implements IntegrationInterface
{
    protected $eventManager;

    public function __construct()
    {
        $this->eventManager = OW::getEventManager();
    }

    public function init(EventManagerInterface $events, ViewerInterface $viewer)
    {
        $this->eventManager->bind("base.on_user_approve", function(OW_Event $event) use($events) {
            $params = $event->getParams();

            $events->emit(
                new UserUpdateEvent($params["userId"])
            );
        });

        $this->eventManager->bind("base.before_save_user", function(OW_Event $event) use($events) {
            $params = $event->getParams();

            /**
             * @var $userDto \BOL_User
             */
            $userDto = $params["dto"];

            $events->emit(
                new UserUpdateEvent($userDto->id)
            );
        });

        $this->eventManager->bind("mailbox.send_message", function(OW_Event $event) use($events) {
            /**
             * @var $messageDto \MAILBOX_BOL_Message
             */
            $messageDto = $event->getData();

            $events->emit(
                new MessageAddedEvent($messageDto->id)
            );
        });

        $this->eventManager->bind("mailbox.onMessageUpdate", function(OW_Event $event) use($events) {
            $params = $event->getParams();

            $events->emit(
                new MessageUpdatedEvent($params["messageId"])
            );
        });

        $this->eventManager->bind("base_add_comment", function(OW_Event $event) use($events) {
            $params = $event->getParams();

            $events->emit(
                new CommentAddedEvent($params["commentId"])
            );
        });

        $this->eventManager->bind("friends.request-sent", function(OW_Event $event) use($events) {
            $params = $event->getParams();

            $events->emit(
                new FriendshipAddedEvent($params["senderId"], $params["recipientId"])
            );
        });

        $this->eventManager->bind("friends.request-accepted", function(OW_Event $event) use($events) {
            $params = $event->getParams();

            $events->emit(
                new FriendshipUpdatedEvent($params["senderId"], $params["recipientId"])
            );
        });

        $this->eventManager->bind("friends.cancelled", function(OW_Event $event) use($events) {
            $params = $event->getParams();
            $friendshipDto = \FRIENDS_BOL_Service::getInstance()->findByRequesterIdAndUserId(
                $params["senderId"], $params["recipientId"]
            );

            $events->emit(
                new FriendshipDeletedEvent(
                    $params["senderId"],
                    $params["recipientId"],

                    /**
                     * TODO: try to get rid of this param to be consistent with other events
                     */
                    $friendshipDto->id
                )
            );
        }, 100);

        /**
         * Add SQL WHERE condition to all user queries to hide currently logged user.
         * 
         * TODO: Find a way to somehow move this logic to UserRepository
         */
        $this->eventManager->bind(
            \BOL_UserService::EVENT_USER_QUERY_FILTER,
            function(\BASE_CLASS_QueryBuilderEvent $event) use($viewer) {
                if (!$viewer->isAuthenticated()) {
                    return;
                }

                $params = $event->getParams();
                $userId = $viewer->getUserId();
                $userTable = $params["tables"][\BASE_CLASS_QueryBuilderEvent::TABLE_USER];
                $userField = $params["fields"][\BASE_CLASS_QueryBuilderEvent::FIELD_USER_ID];

                $event->addWhere("{$userTable}.{$userField} <> $userId");
            }
        );
    }


    public static function getTmpDir() {
        return OW::getPluginManager()->getPlugin('esapi')->getPluginFilesDir();
    }

    public function getConfigRepository()
    {
        return new ConfigRepository();
    }

    public function getUserRepository()
    {
        return new UserRepository();
    }

    public function getPhotoRepository()
    {
        return new PhotoRepository();
    }

    public function getCommentRepository()
    {
        return new CommentRepository();
    }

    public function getAvatarRepository()
    {
        return new AvatarRepository();
    }

    public function getProfileRepository()
    {
        return new ProfileRepository();
    }

    public function getSubscriptionEventsRepository()
    {
        return new SubscriptionRepository();
    }

    public function getChatRepository()
    {
        return new ChatRepository();
    }

    public function getFriendshipRepository()
    {
        return new FriendshipRepository();
    }
}
