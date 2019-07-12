<?php

namespace Iola\Api\Contract\Schema;

interface IDObjectInterface
{
    /**
     * @return string|int
     */
    public function getId();

    /**
     * @return string
     */
    public function getGlobalId();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return mixed
     */
    public function __toString();
}
