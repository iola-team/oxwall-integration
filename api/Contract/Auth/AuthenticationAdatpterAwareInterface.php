<?php
/**
 * Created by PhpStorm.
 * User: skambalin
 * Date: 30.10.17
 * Time: 9.49
 */

namespace Everywhere\Api\Contract\Auth;


interface AuthenticationAdatpterAwareInterface
{
    /**
     * @return AuthenticationAdapterInterface
     */
    public function getAdapter();
}