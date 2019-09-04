<?php

namespace Iola\Api\Contract\Schema;

interface ViewerInterface
{
    /**
     * @return boolean
     */
    public function isAuthenticated();

    /**
     * @return string|null
     */
    public function getUserId();
}