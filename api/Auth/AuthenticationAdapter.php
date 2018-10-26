<?php
/**
 * Created by PhpStorm.
 * User: skambalin
 * Date: 28.10.17
 * Time: 16.36
 */

namespace Everywhere\Api\Auth;


use Everywhere\Api\Contract\Auth\AuthenticationAdapterInterface;
use Everywhere\Api\Contract\Auth\IdentityServiceInterface;
use Everywhere\Api\Contract\Integration\AuthRepositoryInterface;
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