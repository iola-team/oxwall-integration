<?php
/**
 * Created by PhpStorm.
 * User: skambalin
 * Date: 19.10.17
 * Time: 17.37
 */

namespace Everywhere\Oxwall\Integration;

use Everywhere\Api\Contract\App\EventManagerInterface;
use Everywhere\Api\Contract\Integration\SubscriptionRepositoryInterface;
use Everywhere\Api\Contract\Integration\IntegrationInterface;
use Everywhere\Api\Integration\Events\UserApprovedEvent;
use Everywhere\Api\Integration\Events\UserEmailVerifiedEvent;
use Everywhere\Api\Integration\Events\MessageAddedEvent;
use Everywhere\Api\Integration\Events\MessageUpdatedEvent;
use Everywhere\Api\Integration\Events\CommentAddedEvent;
use Everywhere\Api\Integration\Events\SubscriptionEvent;
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

class Integration implements IntegrationInterface
{
    protected $eventManager;

    public function __construct()
    {
        $this->eventManager = OW::getEventManager();
    }

    public function init(EventManagerInterface $events)
    {
        $this->eventManager->bind("moderation.approve", function(OW_Event $event) use($events) {
            $params = $event->getParams();

            $events->emit(
                new UserApprovedEvent($params["entityId"])
            );
        });

        $this->eventManager->bind("base.before_save_user", function(OW_Event $event) use($events) {
            $params = $event->getParams();

            /**
             * @var $userDtoBeforeSave \BOL_User
             */
            $userDtoBeforeSave = $params["dto"];
            /**
             * @var $userDto \BOL_User
             */
            $userDto = $this->getUserRepository()->findById($userDtoBeforeSave->id);

            $events->emit(
                new UserEmailVerifiedEvent($userDto->id)
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
    }


    public static function getTmpDir() {
        return OW::getPluginManager()->getPlugin('esapi')->getPluginFilesDir();
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
