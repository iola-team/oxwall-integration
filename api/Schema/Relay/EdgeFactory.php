<?php

namespace Everywhere\Api\Schema\Relay;

use Everywhere\Api\Contract\Entities\EntityInterface;
use Everywhere\Api\Contract\Schema\Relay\EdgeFactoryInterface;
use Everywhere\Api\Contract\Schema\Relay\EdgeObjectInterface;
use GraphQL\Error\InvariantViolation;
use GraphQL\Executor\Promise\PromiseAdapter;

class EdgeFactory implements EdgeFactoryInterface
{
    protected $promiseAdapter;

    public function __construct(PromiseAdapter $promiseAdapter)
    {
        $this->promiseAdapter = $promiseAdapter;
    }

    protected function getRoot($rootValue)
    {
        return $rootValue;
    }

    protected function getCursor($rootValue, $fromCursor, $direction)
    {
        /**
         * TODO: It is slightly dirty way of getting cursor from `rootValue`. Looks like spaghetti to me. Rethink it in future.
         */
        return isset($rootValue["cursor"]) ? $rootValue["cursor"] : [];
    }

    protected function getNode($rootValue)
    {
        /**
         * TODO: It is slightly dirty way of getting node from `rootValue`. Looks like spaghetti to me. Rethink it in future.
         */
        return isset($rootValue["node"]) ? $rootValue["node"] : $rootValue;
    }

    private function calculateOffset($data, $direction)
    {
        $out = isset($data["offset"]) ? $data["offset"] + $direction : 0;

        return $out < 0 ? 0 : $out;
    }

    private function buildCursor($rootValue, $data, $direction = 0)
    {
        $cursorPromise = $this->promiseAdapter->createFulfilled(
            $this->getCursor($rootValue, $data, $direction)
        );

        return $cursorPromise->then(function($cursor) use($data, $direction) {
            return array_merge($data, $cursor, [
                "offset" => $this->calculateOffset($data, $direction),
            ]);
        });
    }

    private function createEdge($rootValue, $data, $offset = 0)
    {
        return new EdgeObject(
            $this->getRoot($rootValue),
            function() use($rootValue, $data, $offset) {
                return $this->buildCursor($rootValue, $data, $offset);
            },
            function() use($rootValue) {
                return $this->promiseAdapter->createFulfilled($this->getNode($rootValue));
            }
        );
    }

    public function create($data = [], $rootValue = null)
    {
        return $this->createEdge($rootValue, $data, 0);
    }

    public function createBefore($cursor, $rootValue)
    {
        return $this->createEdge($rootValue, $cursor, -1);
    }

    public function createAfter($cursor, $rootValue)
    {
        return $this->createEdge($rootValue, $cursor, 1);
    }

    public function createFromArguments($arguments, $rootValue)
    {
        $paginationArgs = [
            "after" => null, "before" => null, "at" => null
        ];


        $filteredArgs = array_intersect_key($arguments, $paginationArgs);

        if (count($filteredArgs) > 1) {
            throw new InvariantViolation(
                'You can specify only one cursor when creating an edge. The following are provided: '
                . "`" . implode(array_keys($filteredArgs), '`, `') . "`"
            );
        }

        $filteredArgs = array_merge($paginationArgs, $filteredArgs);

        if ($filteredArgs["before"]) {
            return $this->createBefore($filteredArgs["before"], $rootValue);
        }

        if ($filteredArgs["after"]) {
            return $this->createAfter($filteredArgs["after"], $rootValue);
        }

        if ($filteredArgs["at"]) {
            return $this->create($filteredArgs["at"], $rootValue);
        }

        return $this->create([], $rootValue);
    }
}
