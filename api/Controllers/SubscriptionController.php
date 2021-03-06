<?php

namespace Iola\Api\Controllers;

use Iola\Api\Contract\Integration\EventSourceInterface;
use Iola\Api\Contract\Integration\SubscriptionRepositoryInterface;
use Iola\Api\Contract\Subscription\SubscriptionManagerFactoryInterface;
use Iola\Api\Middleware\ServerEvents\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SubscriptionController
{
    protected $manager;
    protected $eventSource;
    protected $subscriptionRepository;

    public function __construct(
        SubscriptionManagerFactoryInterface $managerFactory,
        EventSourceInterface $eventSource,
        SubscriptionRepositoryInterface $subscriptionRepository
    )
    {
        $this->eventSource = $eventSource;
        $this->manager = $managerFactory->create($this->eventSource);
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $streamId = $args["streamId"];
        $body = $request->getParsedBody();
        $subscriptionId = $this->subscriptionRepository->createSubscription($streamId, $body["query"], $body["variables"]);

        return json_encode([
            "subscriptionId" => $subscriptionId
        ]);
    }

    public function delete(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $streamId = $args["streamId"];
        $subscriptionId = $args["subscriptionId"];

        $this->subscriptionRepository->deleteSubscription($subscriptionId);

        return json_encode([
            "deletedId" => $subscriptionId
        ]);
    }

    public function stream(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $streamId = $args["streamId"];
        $subscriptions = $this->subscriptionRepository->findSubscriptionsByStreamId($streamId);

        if (empty($subscriptions)) {
            return $response;
        }

        foreach ($subscriptions as $subscription) {
            $this->manager->subscribe($subscription->query, $subscription->variables, $subscription->id);
        }

        $endTimeStamp = time() + 29;
        $lastEventId = $request->getHeader("Last-Event-ID");
        $lastEventId = empty($lastEventId) ? null : $lastEventId[0];

        /**
         * Close current session to release the session file lock
         */
        session_write_close();

        return $response->withBody(new Stream(
            $this->manager->getIterator(),
            function() use ($endTimeStamp, &$lastEventId) {
              $lastEventId = $this->eventSource->loadEvents($lastEventId);

                /**
                 * Stop streaming if last longer then given time
                 */
                if ($endTimeStamp <= time()) {
                    return false;
                }

                usleep(500000); // Sleep for half a second

                return $lastEventId;
            }
        ));
    }
}
