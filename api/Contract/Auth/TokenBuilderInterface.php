<?php

namespace Iola\Api\Contract\Auth;

use Iola\Api\Auth\Identity;

interface TokenBuilderInterface
{
    /**
     * @param $identity
     * @param $payload
     *
     * @return string
     */
    public function build(Identity $identity, $payload = null);
}