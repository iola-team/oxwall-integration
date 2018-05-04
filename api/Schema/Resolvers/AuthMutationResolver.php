<?php

namespace Everywhere\Api\Schema\Resolvers;

use GraphQL\Error\UserError;
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
        try {
            $user = $this->userRepository->create($args["input"]);
            $identity = $this->identityService->create($user->id);

            return [
                "accessToken" => $this->tokenBuilder->build($identity),
                "user" => $identity->userId,
            ];
        } catch (\Exception $error) {
            switch ($error->getMessage()) {
                case "Duplicate username!":
                    throw new UserError("Duplicate name");
                    break;
                case "Duplicate email!":
                    throw new UserError("Duplicate email");
                    break;
            }
        }
    }

    public function resolveSignIn($root, $args, ContextInterface $context) {
        $adapter = $this->authService->getAdapter();
        $adapter->setIdentity($args["input"]["email"]);
        $adapter->setCredential($args["input"]["password"]);

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
