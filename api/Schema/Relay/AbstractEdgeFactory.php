<?php

namespace Everywhere\Api\Schema\Relay;

use Everywhere\Api\Contract\Entities\EntityInterface;
use Everywhere\Api\Contract\Schema\Relay\EdgeFactoryInterface;
use Everywhere\Api\Contract\Schema\Relay\EdgeObjectInterface;
use GraphQL\Executor\Promise\PromiseAdapter;

abstract class AbstractEdgeFactory implements EdgeFactoryInterface
{
    protected $promiseAdapter;

    public function __construct(PromiseAdapter $promiseAdapter)
    {
        $this->promiseAdapter = $promiseAdapter;
    }

    abstract protected function buildCursor($node, $filter, $offset = 0);

    private function getCursor($node, $filter, $offset = 0)
    {
        return $this->promiseAdapter->createFulfilled($node)->then(function($node) use($filter, $offset) {
            return $this->buildCursor($node, $filter, $offset);
        });
    }

    private function createEdge($node, $filter, $offset = 0)
    {
        return new EdgeObject(
            function($node) use($filter, $offset) {
                return $this->buildCursor($node, $filter, $offset);
            },
            function() use($node) {
                return $this->promiseAdapter->createFulfilled($node);
            }
        );
    }

    public function create($filter, $node = null)
    {
        return $this->createEdge($node, $filter, 0);
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
