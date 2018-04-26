<?php

namespace Everywhere\Api\Schema\Relay;

use Everywhere\Api\Contract\Entities\EntityInterface;
use Everywhere\Api\Contract\Schema\Relay\EdgeObjectInterface;

class EdgeObject implements EdgeObjectInterface
{
    public $cursor;
    public $node;

    public function __construct($cursor, $node = null)
    {
        $this->cursor = $cursor;
        $this->node = $node;
    }

    /**
     * @return EntityInterface|int|null
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * @return array
     */
    public function getCursor()
    {
        return $this->cursor;
    }
}
