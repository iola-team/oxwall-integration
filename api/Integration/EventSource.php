<?php

namespace Iola\Api\Integration;

use Iola\Api\App\EventManager;
use Iola\Api\Contract\Integration\Events\SubscriptionEventInterface;
use Iola\Api\Contract\Integration\EventSourceInterface;
use Iola\Api\Contract\Integration\SubscriptionRepositoryInterface;
use Iola\Api\Integration\Events\SubscriptionEvent;
use League\Event\Emitter;
use League\Event\EventInterface;
use League\Event\ListenerAcceptor;
use League\Event\ListenerAcceptorInterface;
use League\Event\ListenerProviderInterface;

class EventSource extends EventManager implements EventSourceInterface, ListenerProviderInterface
{
    /**
     * @var SubscriptionRepositoryInterface
     */
    protected $eventsRepository;

    /**
     * Event lifetime in milliseconds
     *
     * @var int
     */
    protected $eventLifetime = 5000;

    public function __construct(SubscriptionRepositoryInterface $eventsRepository)
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
        $eventEntities = $this->eventsRepository->findEvents(
            $timeOffset ?: $this->getTimeOffset()
        );

        $lastEventTimeOffset = null;
        foreach ($eventEntities as $eventEntity) {
            $this->emit(
                new SubscriptionEvent($eventEntity->name, $eventEntity->data)
            );

            $lastEventTimeOffset = $eventEntity->timeOffset;
        }

        return $lastEventTimeOffset ?: $this->getTimeOffset();
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
