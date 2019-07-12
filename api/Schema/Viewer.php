<?php

namespace Iola\Api\Schema;

use Iola\Api\Contract\Auth\AuthenticationServiceInterface;
use Iola\Api\Contract\Schema\ViewerInterface;

class Viewer implements ViewerInterface
{
    /**
     * @var AuthenticationServiceInterface
     */
    protected $authService;

    public function __construct(AuthenticationServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function getUserId()
    {
        return $this->authService->hasIdentity()
            ? $this->authService->getIdentity()->userId
            : null;
    }

    public function isAuthenticated()
    {
        return $this->authService->hasIdentity();
    }
}