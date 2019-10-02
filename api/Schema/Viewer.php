<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Schema;

use Iola\Api\Contract\Auth\AuthenticationServiceInterface;
use Iola\Api\Contract\Schema\ViewerInterface;

class Viewer implements ViewerInterface
{
    /**
     * @var AuthenticationServiceInterface
     */
    protected $authService;

    public function __construct(AuthenticationServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function getUserId()
    {
        return $this->authService->hasIdentity()
            ? (string) $this->authService->getIdentity()->userId
            : null;
    }

    public function isAuthenticated()
    {
        return $this->authService->hasIdentity();
    }
}