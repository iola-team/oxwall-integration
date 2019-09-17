<?php

namespace Iola\Oxwall;

use Iola\Api\Contract\App\EventManagerInterface;
use Iola\Api\Contract\Integration\IntegrationInterface;
use Iola\Api\Contract\Schema\ViewerInterface;

use Iola\Api\App\Events\BeforeRequestEvent;
use Iola\Api\Integration\Events\UserUpdateEvent;
use Iola\Api\Integration\Events\MessageAddedEvent;
use Iola\Api\Integration\Events\MessageUpdatedEvent;
use Iola\Api\Integration\Events\CommentAddedEvent;
use Iola\Api\Integration\Events\FriendshipUpdatedEvent;
use Iola\Api\Integration\Events\FriendshipAddedEvent;
use Iola\Api\Integration\Events\FriendshipDeletedEvent;

use Iola\Oxwall\Authenticator;
use Iola\Oxwall\AuthAdapter;

use Iola\Oxwall\Repositories\ConfigRepository;
use Iola\Oxwall\Repositories\AvatarRepository;
use Iola\Oxwall\Repositories\ChatRepository;
use Iola\Oxwall\Repositories\ProfileRepository;
use Iola\Oxwall\Repositories\SubscriptionRepository;
use Iola\Oxwall\Repositories\UserRepository;
use Iola\Oxwall\Repositories\PhotoRepository;
use Iola\Oxwall\Repositories\CommentRepository;
use Iola\Oxwall\Repositories\FriendshipRepository;
use Iola\Oxwall\Repositories\ReportRepository;

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
         * API event handlers
         */
         $events->addListener(BeforeRequestEvent::EVENT_NAME, function(BeforeRequestEvent $event) {
             $this->onBeforeRequest($event->getViewer());
         });
    }

    public function onBeforeRequest(ViewerInterface $viewer)
    {
        /**
         * Override built in authenticator
         */
        \OW_Auth::getInstance()->setAuthenticator(new Authenticator($viewer));

        /**
         * Trigger user authentication process with always failing auth adapter.
         * It will reset OW_User internal cahce and read data from our Authenticator
         * 
         * TODO: Remove when possible
         */
        OW::getUser()->authenticate(new AuthAdapter());
    }

    public static function getTmpDir() {
        return OW::getPluginManager()->getPlugin('iola')->getPluginFilesDir();
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

    public function getReportRepository()
    {
        return new ReportRepository();
    }
}
