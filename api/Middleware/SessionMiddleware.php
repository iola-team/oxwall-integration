<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Iola\Api\Contract\Integration\UserRepositoryInterface;
use Iola\Api\Contract\Auth\AuthenticationServiceInterface;

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
