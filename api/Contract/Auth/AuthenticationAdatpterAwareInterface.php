<?php

namespace Iola\Api\Contract\Auth;

interface AuthenticationAdatpterAwareInterface
{
    /**
     * @return AuthenticationAdapterInterface
     */
    public function getAdapter();
}