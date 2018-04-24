<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Auth\AuthenticationServiceInterface;
use Everywhere\Api\Contract\Auth\IdentityServiceInterface;
use Everywhere\Api\Contract\Auth\TokenBuilderInterface;
use Everywhere\Api\Contract\Schema\ContextInterface;
use Everywhere\Api\Schema\CompositeResolver;
use Everywhere\Api\Contract\Integration\UserRepositoryInterface;

class AuthMutationResolver extends CompositeResolver
{
    /**
     * @var AuthenticationServiceInterface
     */
    protected $authService;

    /**
     * @var TokenBuilderInterface
     */
    protected $tokenBuilder;

    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    public function __construct(
        AuthenticationServiceInterface $authService,
        TokenBuilderInterface $tokenBuilder,
        IdentityServiceInterface $identityService,
        UserRepositoryInterface $userRepository
    ) {
        parent::__construct([
            "signUpUser" => [$this, "resolveSignUp"],
            "signInUser" => [$this, "resolveSignIn"]
        ]);

        $this->authService = $authService;
        $this->tokenBuilder = $tokenBuilder;
        $this->identityService = $identityService;
        $this->userRepository = $userRepository;
    }

    public function resolveSignUp($root, $args, ContextInterface $context) {
        $user = $this->userRepository->create($args["input"]); // @TODO: $args["input"] validation?
        // @TODO: handle errors such as "Duplicate username!" or "Duplicate email!"
        $identity = $this->identityService->create($user->id); // @TODO: $issueTime and $expirationTime for token?

        return [
            "accessToken" => $this->tokenBuilder->build($identity),
            "user" => $user
        ];
    }

    public function resolveSignIn($root, $args, ContextInterface $context) {
        $adapter = $this->authService->getAdapter();
        $adapter->setIdentity($args["login"]);
        $adapter->setCredential($args["password"]);

        $result = $this->authService->authenticate();

        if (!$result->isValid()) {
            return [
                "accessToken" => null,
                "user" => null
            ];
        }

        $identity = $this->authService->getIdentity();

        return [
            "accessToken" => $this->tokenBuilder->build($identity),
            "user" => $identity->userId
        ];
    }
}
