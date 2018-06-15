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

    protected function buildCursor($node, $fromCursor, $direction)
    {
        return [];
    }

    protected function loadNode($node)
    {
        return $node;
    }

    private function calculateOffset($data, $direction)
    {
        $out = isset($data["offset"]) ? $data["offset"] + $direction : 0;

        return $out < 0 ? 0 : $out;
    }

    private function getCursor($node, $data, $direction = 0)
    {
        $nodePromise = $this->promiseAdapter->createFulfilled($node)->then(function($node) {
            return $this->loadNode($node);
        });

        return $nodePromise->then(function($node) use($data, $direction) {
            return array_merge($data, $this->buildCursor($node, $data, $direction), [
                "offset" => $this->calculateOffset($data, $direction),
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
