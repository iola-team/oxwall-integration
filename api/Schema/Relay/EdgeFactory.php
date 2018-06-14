<?php

namespace Everywhere\Api\Schema\Relay;

use Everywhere\Api\Contract\Entities\EntityInterface;
use Everywhere\Api\Contract\Schema\Relay\EdgeFactoryInterface;
use Everywhere\Api\Contract\Schema\Relay\EdgeObjectInterface;
use GraphQL\Executor\Promise\PromiseAdapter;

class EdgeFactory implements EdgeFactoryInterface
{
    protected $promiseAdapter;

    public function __construct(PromiseAdapter $promiseAdapter)
    {
        $this->promiseAdapter = $promiseAdapter;
    }

    protected function buildCursor($node, $fromCursor, $offset)
    {
        return [
            "offset" => isset($fromCursor["offset"]) ? $fromCursor["offset"] + $offset : 0
        ];
    }

    protected function loadNode($node)
    {
        return $node;
    }

    private function getCursor($node, $data, $offset = 0)
    {
        $nodePromise = $this->promiseAdapter->createFulfilled($node)->then(function($node) {
            return $this->loadNode($node);
        });

        return $nodePromise->then(function($node) use($data, $offset) {
            $fromCursor = isset($data["cursor"]) ? $data["cursor"] : null;

            return array_merge($data, [
                "offset" => isset($data["offset"]) ? $data["offset"] + $offset : 0,
                "cursor" => $this->buildCursor($node, $fromCursor, $offset),
            ]);
        });
    }

    private function createEdge($node, $data, $offset = 0)
    {
        return new EdgeObject(
            function($node) use($data, $offset) {
                return $this->getCursor($node, $data, $offset);
            },
            function() use($node) {
                return $this->promiseAdapter->createFulfilled($node);
            }
        );
    }

    public function create($data = [], $node = null)
    {
        return $this->createEdge($node, $data, 0);
    }

    public function createBefore($cursor, $node)
    {
        return $this->createEdge($node, $cursor, -1);
    }

    public function createAfter($cursor, $node)
    {
        return $this->createEdge($node, $cursor, 1);
    }

}
