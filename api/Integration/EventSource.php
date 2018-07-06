<?php

namespace Everywhere\Api\Integration;

use Everywhere\Api\App\EventManager;
use Everywhere\Api\Contract\Integration\Events\SubscriptionEventInterface;
use Everywhere\Api\Contract\Integration\EventSourceInterface;
use Everywhere\Api\Contract\Integration\SubscriptionEventsRepositoryInterface;
use Everywhere\Api\Integration\Events\SubscriptionEvent;
use League\Event\Emitter;
use League\Event\EventInterface;
use League\Event\ListenerAcceptor;
use League\Event\ListenerAcceptorInterface;
use League\Event\ListenerProviderInterface;

class EventSource extends EventManager implements EventSourceInterface, ListenerProviderInterface
{
    /**
     * @var SubscriptionEventsRepositoryInterface
     */
    protected $eventsRepository;

    /**
     * Event lifetime in milliseconds
     *
     * @var int
     */
    protected $eventLifetime = 5000;

    public function __construct(SubscriptionEventsRepositoryInterface $eventsRepository)
    {
        $this->eventsRepository = $eventsRepository;
    }

    protected function getTimeOffset()
    {
        $offsetBase = strtotime("01/01/2018") * 1000;

        return round(microtime(true) * 1000) - $offsetBase;
    }

    /**
     * @param SubscriptionEventInterface $event
     */
    protected function handle($event)
    {
        $this->eventsRepository->deleteEvents($this->getTimeOffset() - $this->eventLifetime);
        $this->eventsRepository->addEvent(
            $event->getName(),
            $event->getData(),
            $this->getTimeOffset()
        );
    }

    public function loadEvents($timeOffset = null)
    {
        $lastEventTimeOffset = $timeOffset ? $timeOffset : $this->getTimeOffset();
        $eventEntities = $this->eventsRepository->findEvents($lastEventTimeOffset);

        foreach ($eventEntities as $eventEntity) {
            $this->emit(
                new SubscriptionEvent($eventEntity->name, $eventEntity->data)
            );

            $lastEventTimeOffset = $eventEntity->timeOffset;
        }

        return $lastEventTimeOffset;
    }

    public function provideListeners(ListenerAcceptorInterface $listenerAcceptor)
    {
        $listenerAcceptor->addListener("*", function($event) {
            if ($event instanceof SubscriptionEventInterface) {
                $this->handle($event);
            }
        });
    }
}
