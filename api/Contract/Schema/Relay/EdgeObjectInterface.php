<?php

namespace Everywhere\Api\Contract\Schema\Relay;

use Everywhere\Api\Contract\Entities\EntityInterface;

interface EdgeObjectInterface
{
    /**
     * @return EntityInterface|null
     */
    public function getNode();

    /**
     * @return array
     */
    public function getCursor();
}
