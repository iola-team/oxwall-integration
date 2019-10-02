<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Middleware;

use Iola\Api\Contract\Auth\IdentityServiceInterface;
use Iola\Api\Contract\Auth\IdentityStorageInterface;
use Iola\Api\Contract\Auth\TokenBuilderInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Middleware\JwtAuthentication;

class AuthenticationMiddleware extends JwtAuthentication
{
    const ATTRIBUTE_NAME = "JWTTokenData";

    /**
     * @var IdentityStorageInterface
     */
    protected $identityStorage;

    /**
     * @var TokenBuilderInterface
     */
    protected $tokenBuilder;

    /**
     * @var IdentityServiceInterface
     */
    protected $identityService;

    public function __construct(
        $options,
        IdentityStorageInterface $identityStorage,
        IdentityServiceInterface $identityService,
        TokenBuilderInterface $tokenBuilder
    )
    {
        $options = [
            "secure" => false,
            "secret" => empty($options["secret"]) ? null : $options["secret"],
            "attribute" => self::ATTRIBUTE_NAME,
            "callback" => function($request, $response, $args) use($identityStorage) {
                $identityStorage->write(
                    empty($args["decoded"]) ? null : $this->createIdentity($args["decoded"])
                );
            }
        ];

        $this->identityStorage = $identityStorage;
        $this->tokenBuilder = $tokenBuilder;
        $this->identityService = $identityService;

        parent::__construct($options);
    }

    protected function createIdentity($tokenData)
    {
        return $this->identityService->create(
            $tokenData->userId,
            $tokenData->iat,
            $tokenData->exp
        );
    }

    public function decodeToken($token)
    {
        $decoded = $token ? parent::decodeToken($token) : null;

        /**
         * Return `null` instead of `false` to prevent 401 response
         */
        return $decoded ?: null;
    }

    public function fetchToken(RequestInterface $request)
    {
        /**
         * Return `null` instead of `false` to prevent 401 response
         */
        return parent::fetchToken($request) ?: null;
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        $resultResponse = parent::__invoke($request, $response, $next);

        if (!$this->identityStorage->isEmpty()) {
            $resultResponse = $resultResponse->withHeader(
                $this->getHeader(),
                $this->tokenBuilder->build($this->identityStorage->read())
            );
        }

        return $resultResponse;
    }
}