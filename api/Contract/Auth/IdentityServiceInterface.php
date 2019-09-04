<?php

namespace Iola\Api\Contract\Auth;

use Iola\Api\Auth\Identity;

interface IdentityServiceInterface
{
    /**
     * @param int|string $userId
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