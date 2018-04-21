<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Auth\IdentityServiceInterface;
use Everywhere\Api\Contract\Auth\TokenBuilderInterface;
use Everywhere\Api\Contract\Integration\UserRepositoryInterface;
use Everywhere\Api\Contract\Schema\ContextInterface;
use Everywhere\Api\Schema\CompositeResolver;
use Everywhere\Api\Schema\IDObject;

class UserMutationResolver extends CompositeResolver
{
    /**
     * @var IdentityServiceInterface
     */
    protected $identityService;

    /**
     * @var TokenBuilderInterface
     */
    protected $tokenBuilder;

    public function __construct(UserRepositoryInterface $userRepository, IdentityServiceInterface $identityService, TokenBuilderInterface $tokenBuilder)
    {
        parent::__construct([
            "signUpUser" => [$this, "resolveSignUp"]
        ]);

        $this->identityService = $identityService;
        $this->tokenBuilder = $tokenBuilder;
    }

    public function resolveSignUp($root, $args, ContextInterface $context) {
        return [
            "accessToken" => 'you are an admin now',
        ];
    }
}
