<?php

namespace Everywhere\Api\Contract\Auth;

use Everywhere\Api\Auth\Identity;

interface IdentityServiceInterface
{
    /**
     * @param mixed $userId
     * @param int|null $issueTime
     * @param int|null $expirationTime
     *
     * @return Identity
     */
    public function create($userId, $issueTime = null, $expirationTime = null);

    /**
     * @param Identity $identity
     *
     * @return Identity
     */
    public function renew(Identity $identity);
}