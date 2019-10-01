<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Schema\Relay;

use Iola\Api\Contract\Schema\Relay\EdgeObjectInterface;
use GraphQL\Error\InvariantViolation;
use GraphQL\Executor\Promise\Promise;

class EdgeObject implements EdgeObjectInterface, \ArrayAccess
{
    protected $cursorGetter;
    protected $nodeGetter;
    protected $rootValue;

    public function __construct($rootValue, callable $cursorGetter, callable $nodeGetter)
    {
        $this->rootValue = $rootValue;
        $this->cursorGetter = $cursorGetter;
        $this->nodeGetter = $nodeGetter;
    }

    public function getRootValue()
    {
        return $this->rootValue;
    }

    private function getFromRootValue($name, $defaultValue = null)
    {
        $rootValue = empty($this->rootValue) ? [] : (array) $this->rootValue;

        return isset($rootValue[$name]) ? $rootValue[$name] : $defaultValue;
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
        return call_user_func($this->cursorGetter);
    }

    public function offsetExists($offset)
    {
        $rootValueKeys = is_array($this->rootValue) ? array_keys($this->rootValue) : [];

        return in_array($offset, array_merge($rootValueKeys, ['cursor', 'node']));
    }

    public function offsetGet($offset)
    {
        if ($offset === "cursor") {
            return $this->getCursor();
        }

        if ($offset === "node") {
            return $this->getNode();
        }

        return $this->getFromRootValue($offset);
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
