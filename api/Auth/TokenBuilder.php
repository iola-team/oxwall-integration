<?php

namespace Iola\Api\Auth;

use Iola\Api\Contract\Auth\TokenBuilderInterface;
use Firebase\JWT\JWT;

class TokenBuilder implements TokenBuilderInterface
{
    protected $options = [
        "lifeTime" => 600, // 10 min
        "secret" => null
    ];

    public function __construct(array $options)
    {
        $this->options = array_merge($this->options, $options);
    }

    public function build(Identity $identity, $payload = null)
    {
        $now = time();

        return JWT::encode([
            "iat" => $identity->issueTime ?: $now,
            "exp" => $identity->expirationTime ?: $now + $this->options["lifeTime"],
            "userId" => $identity->userId
        ], $this->options["secret"]);
    }
}