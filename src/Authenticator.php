<?php

namespace Everywhere\Oxwall;

use Everywhere\Api\Contract\Schema\ViewerInterface;

class Authenticator implements \OW_IAuthenticator
{
    use \OW_Singleton;

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
