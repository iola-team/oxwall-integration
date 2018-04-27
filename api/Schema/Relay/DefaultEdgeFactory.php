<?php

namespace Everywhere\Api\Schema\Relay;

use GraphQL\Executor\Promise\PromiseAdapter;

class DefaultEdgeFactory extends AbstractEdgeFactory
{
    protected function buildCursor($node, $filter, $offset = 0)
    {
        $newOffset = isset($filter["offset"]) ? $filter["offset"] + $offset : 0;

        return array_merge($filter, [
            "offset" => $newOffset < 0 ? 0 : $newOffset
        ]);
    }
}
