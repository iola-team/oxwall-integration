<?php

namespace Iola\Api\Auth;

use Iola\Api\Contract\Auth\IdentityServiceInterface;

class IdentityService implements IdentityServiceInterface
{
    protected $options = [
        "lifeTime" => 6000
    ];

    public function __construct(array $options)
    {
        $this->options = array_merge($this->options, $options);
    }

    public function create($userId, $issueTime = null, $expirationTime = null)
    {
        if (empty($userId)) {
            return null;
        }

        $identity = new Identity();
        $identity->userId = $userId;
        $identity->issueTime = (int) $issueTime ?: time();
        $identity->expirationTime = (int) $expirationTime ?: $identity->issueTime + $this->options["lifeTime"];

        return $identity;
    }

    public function renew(Identity $identity)
    {
        return $this->create($identity->userId);
    }
}