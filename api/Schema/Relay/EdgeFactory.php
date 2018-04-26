<?php

namespace Everywhere\Api\Schema\Relay;

use Everywhere\Api\Contract\Entities\EntityInterface;
use Everywhere\Api\Contract\Schema\Relay\EdgeFactoryInterface;
use Everywhere\Api\Contract\Schema\Relay\EdgeObjectInterface;

class EdgeFactory implements EdgeFactoryInterface
{
    protected function createCursor($filterData, $offset = 0)
    {
        return array_merge($filterData, [
            "offset" => isset($filterData["offset"]) ? $filterData["offset"] + $offset : 0
        ]);
    }

    public function create($node, $filterArguments)
    {
        return new EdgeObject(
            $this->createCursor($filterArguments, 0),
            $node
        );
    }

    public function createBefore($node, $cursor)
    {
        return new EdgeObject(
            $this->createCursor($cursor, -1),
            $node
        );
    }

    public function createAfter($node, $cursor)
    {
        return new EdgeObject(
            $this->createCursor($cursor, 1),
            $node
        );
    }

}
