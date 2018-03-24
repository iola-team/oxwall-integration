<?php

namespace Everywhere\Api\Schema;

use Everywhere\Api\Contract\Schema\ConnectionObjectInterface;
use GraphQL\Error\InvariantViolation;
use GraphQL\Utils;

class ConnectionObject implements ConnectionObjectInterface
{
    protected $root;
    protected $arguments;
    protected $itemsGetter;
    protected $countGetter;

    public function __construct($root, $arguments, callable $itemsGetter = null, callable $countGetter = null)
    {
        $this->root = $root;
        $this->arguments = $arguments;
        $this->itemsGetter = $itemsGetter;
        $this->countGetter = $countGetter;
    }

    public function getItems($arguments = null)
    {
        $getter = $this->itemsGetter;

        if (!$getter) {
            throw new InvariantViolation(
                "Connection can not load items due to invalid loader: " . Utils::printSafe($getter)
            );
        }

        return $getter($arguments ? $arguments : $this->arguments);
    }

    public function getCount($arguments = null)
    {
        $getter = $this->countGetter;

        if (!$getter) {
            throw new InvariantViolation(
                "Connection can not load items count due to invalid loader: " . Utils::printSafe($getter)
            );
        }

        return $getter($arguments ? $arguments : $this->arguments);
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function getRoot()
    {
        return $this->root;
    }

    public function __toString()
    {
        return spl_object_hash($this);
    }
}
