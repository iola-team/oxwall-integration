<?php

namespace Everywhere\Api\Schema\Relay;

use Everywhere\Api\Contract\Entities\EntityInterface;
use Everywhere\Api\Contract\Schema\Relay\EdgeObjectInterface;
use GraphQL\Error\InvariantViolation;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Utils;

class EdgeObject implements EdgeObjectInterface, \ArrayAccess
{
    protected $cursorGetter;
    protected $nodeGetter;

    public function __construct(callable $cursorGetter, callable $nodeGetter = null)
    {
        $this->cursorGetter = $cursorGetter;
        $this->nodeGetter = $nodeGetter;
    }

    /**
     * @return Promise
     */
    public function getNode()
    {
        return call_user_func($this->nodeGetter);
    }

    /**
     * @return Promise
     */
    public function getCursor()
    {
        return $this->getNode()->then($this->cursorGetter);
    }

    public function offsetExists($offset)
    {
        return in_array($offset, ['cursor', 'node']);
    }

    public function offsetGet($offset)
    {
        if ($offset === "cursor") {
            return $this->getCursor();
        }

        if ($offset === "node") {
            return $this->getNode();
        }
    }

    private function throwAccessError()
    {
        throw new InvariantViolation(
            "You should not edit `EdgeObject` properties directly!"
        );
    }

    public function offsetUnset($offset)
    {
        $this->throwAccessError();
    }

    public function offsetSet($offset, $value)
    {
        $this->throwAccessError();
    }
}
