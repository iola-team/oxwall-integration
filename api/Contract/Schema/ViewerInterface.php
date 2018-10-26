<?php

namespace Everywhere\Api\Contract\Schema;

interface ViewerInterface
{
    /**
     * @return boolean
     */
    public function isAuthenticated();

    /**
     * @return mixed
     */
    public function getUserId();
}