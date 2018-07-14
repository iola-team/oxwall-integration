<?php

namespace Everywhere\Api\Contract\Integration;


interface AuthRepositoryInterface
{
    /**
     * @param string $login
     * @param string $password
     *
     * @return mixed
     */
    public function authenticate($login, $password);
}
