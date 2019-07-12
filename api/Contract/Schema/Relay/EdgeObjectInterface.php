<?php

namespace Iola\Api\Contract\Schema\Relay;

use GraphQL\Executor\Promise\Promise;

interface EdgeObjectInterface
{
    /**
     *
     * @return mixed
     */
    public function getRootValue();

    /**
     * @return Promise
     */
    public function getNode();

    /**
     * @return Promise
     */
    public function getCursor();
}
