<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Oxwall;

use Iola\Api\Contract\Schema\ViewerInterface;
use OW_IAuthenticator;
use OW_Singleton;

class Authenticator implements OW_IAuthenticator
{
    use OW_Singleton;

    /**
     * @var ViewerInterface
     */
    protected $viewer;

    public function __construct(ViewerInterface $viewer)
    {
        $this->viewer = $viewer;
    }

    public function isAuthenticated()
    {
        return $this->viewer->isAuthenticated();
    }

    public function getUserId()
    {
        return $this->viewer->getUserId();
    }

    public function login( $userId )
    {
        // Do nothing
    }

    public function logout()
    {
        // Do nothing
    }

    public function getId()
    {
        return null;
    }
}
