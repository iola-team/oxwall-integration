<?php

namespace Everywhere\Api\Contract\Schema\Relay;

use Everywhere\Api\Contract\Entities\EntityInterface;
use GraphQL\Executor\Promise\Promise;

interface EdgeObjectInterface
{
    /**
     * @return Promise
     */
    public function getNode();

    /**
     * @return Promise
     */
    public function getCursor();
}
