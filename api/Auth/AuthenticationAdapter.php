<?php

namespace Iola\Api\Auth;

use Iola\Api\Contract\Auth\AuthenticationAdapterInterface;
use Iola\Api\Contract\Auth\IdentityServiceInterface;
use Iola\Api\Contract\Integration\AuthRepositoryInterface;
use Zend\Authentication\Adapter\Callback;

class AuthenticationAdapter extends Callback implements AuthenticationAdapterInterface
{
    public function __construct(
        AuthRepositoryInterface $authRepository,
        IdentityServiceInterface $identityService
    )
    {
        $this->setCallback(function($identity, $credentials) use($authRepository, $identityService) {
            return $identityService->create(
                $authRepository->authenticate($identity, $credentials)
            );
        });
    }
}