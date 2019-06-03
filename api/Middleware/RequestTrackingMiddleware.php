<?php

namespace Everywhere\Api\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Everywhere\Api\Contract\Schema\ContextInterface;
use Everywhere\Api\Contract\App\EventManagerInterface;
use Everywhere\Api\App\Events\BeforeRequestEvent;
use Everywhere\Api\App\Events\AfterRequestEvent;

class RequestTrackingMiddleware
{
    /**
     * @var ContextInterface
     */
    protected $context;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    public function __construct(ContextInterface $context, EventManagerInterface $eventManager)
    {
        $this->context = $context;
        $this->eventManager = $eventManager;
    }

    protected function onBeforeRequest(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->eventManager->emit(
            new BeforeRequestEvent($this->context->getViewer())
        );
    }

    protected function onAfterRequest(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->eventManager->emit(
            new AfterRequestEvent($this->context->getViewer())
        );
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $this->onBeforeRequest($request, $response);
        $nextResponse = $next($request, $response);
        $this->onAfterRequest($request, $nextResponse);

        return $nextResponse;
    }
}
