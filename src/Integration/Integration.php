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
use Everywhere\Api\Integration\Events\NewMessageEvent;
use Everywhere\Api\Integration\Events\SubscriptionEvent;
use Everywhere\Oxwall\Integration\Repositories\AvatarRepository;
use Everywhere\Oxwall\Integration\Repositories\ChatRepository;
use Everywhere\Oxwall\Integration\Repositories\ProfileRepository;
use Everywhere\Oxwall\Integration\Repositories\SubscriptionRepository;
use Everywhere\Oxwall\Integration\Repositories\UserRepository;
use Everywhere\Oxwall\Integration\Repositories\PhotoRepository;
use Everywhere\Oxwall\Integration\Repositories\CommentRepository;

class Integration implements IntegrationInterface
{
    protected $eventManager;

    public function __construct()
    {
        $this->eventManager = \OW::getEventManager();
    }

    public function init(EventManagerInterface $events)
    {
        $this->eventManager->bind("mailbox.send_message", function(\OW_Event $event) use($events) {
            /**
             * @var $messageDto \MAILBOX_BOL_Message
             */
            $messageDto = $event->getData();

            $events->emit(
                new NewMessageEvent($messageDto->senderId, $messageDto->conversationId, $messageDto->id)
            );
        });
    }


    public static function getTmpDir() {
        return \OW::getPluginManager()->getPlugin('esapi')->getPluginFilesDir();
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
}
