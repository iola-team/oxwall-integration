<?php

namespace Iola\Api\Schema;

use Iola\Api\Contract\Schema\IDObjectInterface;

class IDObject implements IDObjectInterface
{
    protected $id;
    protected $type;

    protected $globalIdBuilder;

    public function __construct($typeName, $id, callable $globalIdBuilder)
    {
        $this->type = $typeName;
        $this->id = $id;
        $this->globalIdBuilder = $globalIdBuilder;
    }

    public function getId()
    {
        return (string) $this->id;
    }

    public function getGlobalId()
    {
        return call_user_func($this->globalIdBuilder, $this->getType(), $this->getId());
    }

    public function getType()
    {
        return $this->type;
    }

    public function __toString()
    {
        return $this->getGlobalId();
    }
}
