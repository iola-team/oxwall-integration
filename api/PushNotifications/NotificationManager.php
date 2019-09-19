<?php

namespace Iola\Api\PushNotifications;

use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use GraphQL\Executor\Promise\Promise;
use League\Event\ListenerAcceptorInterface;
use Iola\Api\Contract\App\EventInterface;
use Iola\Api\Contract\PushNotifications\NotificationManagerInterface;
use Iola\Api\Contract\PushNotifications\NotificationHandlerInterface;
use Iola\Api\Contract\PushNotifications\NotificationPusherInterface;

class NotificationManager implements NotificationManagerInterface
{
    /**
     * @var NotificationHandlerInterface[]
     */
    protected $handlers = [];

    /**
     * @var NotificationPusherInterface[]
     */
    protected $pushers = [];

    /**
     * @var SyncPromiseAdapter
     */
    protected $promiseAdapter;

    public function __construct(array $options, SyncPromiseAdapter $promiseAdapter, callable $resolve)
    {
        $this->promiseAdapter = $promiseAdapter;
        $this->handlers = array_filter(array_map($resolve, $options["handlers"]));
        $this->pushers = array_filter(array_map($resolve, $options["pushers"]));
    }

    /**
     * @param mixed $value
     * @return Promise
     */
    private function toPromse($value)
    {
        return $this->promiseAdapter->createFulfilled($value);
    }

    /**
     * @param EventInterface $event
     * @return void
     */
    protected function handleEvent($event)
    {
        $notifications = [];
        $handlerPromises = [];
        foreach ($this->handlers as $handler) {
            $shouldHandle = $this->toPromse($handler->shouldHandleEvent($event));
            $userIds = $shouldHandle->then(function($should) use($handler, $event) {
                return $should ? $this->toPromse($handler->getTargetUserIds($event)) : [];
            });

            $handlerPromises[] = $userIds->then(function($userIds) use($handler, $event, &$notifications) {
                $promises = [];

                foreach ($userIds as $userId) {
                    $createPromise = $this->toPromse($handler->createNotification($userId, $event));
                    $promises[] = $createPromise->then(function($notification) use(&$notifications, $userId) {
                        if ($notification) {
                            $notifications[$userId] = isset($notifications[$userId]) 
                                ? $notifications[$userId]
                                : [];

                            $notifications[$userId][] = $notification;
                        }
                    });
                }

                return $this->promiseAdapter->all($promises);
            });
        }

        $this->promiseAdapter->wait($this->promiseAdapter->all($handlerPromises));
        $this->push($notifications);
    }

    protected function push($notifications)
    {
        $userIds = array_keys($notifications);
        $deviceIds = $this->getDeviceIds($userIds);

        foreach ($this->pushers as $pusher) {
            foreach ($notifications as $userId => $notifications) {
                if (empty($deviceIds[$userId])) {
                    continue;
                }

                $pusher->push($deviceIds[$userId], $notifications);
            }
        }
    }

    protected function getDeviceIds($userIds)
    {
        $deviceIds = array_fill_keys($userIds, []);

        return $deviceIds;
    }

    /**
     * @param ListenerAcceptorInterface $listenerAcceptor
     * @return EventListenerProvider
     */
    public function provideListeners(ListenerAcceptorInterface $listenerAcceptor)
    {
        $listenerAcceptor->addListener(
            "*",
            function($event) {
                $this->handleEvent($event);
            }
        );

        return $this;
    }
}