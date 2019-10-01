<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

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
        $identity->userId = (string) $userId;
        $identity->issueTime = (int) $issueTime ?: time();
        $identity->expirationTime = (int) $expirationTime ?: $identity->issueTime + $this->options["lifeTime"];

        return $identity;
    }

    public function renew(Identity $identity)
    {
        return $this->create($identity->userId);
    }
}