<?php

namespace Everywhere\Api\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Everywhere\Api\Contract\Integration\UserRepositoryInterface;
use Everywhere\Api\Contract\Auth\AuthenticationServiceInterface;

/**
 * TODO: Not an optimal solution, try to find a better one
 */
class SessionMiddleware
{
    /**
     *
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     *
     * @var AuthenticationServiceInterface
     */
    private $authService;

    public function __construct(
        AuthenticationServiceInterface $authService,
        UserRepositoryInterface $userRepository
    ) {
        $this->authService = $authService;
        $this->userRepository = $userRepository;
    }

    private function onBeforeRequest(ServerRequestInterface $request, ResponseInterface $response)
    {
        if (!$this->authService->hasIdentity()) {
            return;
        }

        $identity = $this->authService->getIdentity();
        $this->userRepository->trackUserActivity($identity->userId);
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $this->onBeforeRequest($request, $response);

        return $next($request, $response);
    }
}
