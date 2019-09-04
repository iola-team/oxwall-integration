<?php

namespace Iola\Api\Contract\Schema;

interface ViewerInterface
{
    /**
     * @return boolean
     */
    public function isAuthenticated();

    /**
     * @return string
     */
    public function getUserId();
}