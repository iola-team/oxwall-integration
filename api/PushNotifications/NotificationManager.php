<?php

namespace Iola\Api\PushNotifications;

use League\Event\ListenerAcceptorInterface;
use Iola\Api\Contract\App\EventInterface;
use Iola\Api\Contract\PushNotifications\NotificationManagerInterface;
use Iola\Api\Contract\PushNotifications\NotificationEventInterface;

class NotificationManager implements NotificationManagerInterface
{
    /**
     * @param EventInterface $event
     * @return void
     */
    protected function handleEvent($event)
    {

    }

    /**
     * @param EventInterface $event
     * @return boolean
     */
    protected function shouldHandleEvent($event)
    {
        return $event instanceof NotificationEventInterface;
    }

    /**
     * @param ListenerAcceptorInterface $listenerAcceptor
     * @return EventListenerProvider
     */
    public function provideListeners($listenerAcceptor)
    {
        $listenerAcceptor->addListener(
            "*",
            function(EventInterface $event) {
                if ($this->shouldHandleEvent($event)) {
                    $this->handleEvent($event);
                }
            }
        );

        return $this;
    }
}