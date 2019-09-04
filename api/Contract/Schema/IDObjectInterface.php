<?php

namespace Iola\Api\Contract\Schema;

interface IDObjectInterface
{
    /**
     * @return string
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
     * @return string
     */
    public function __toString();
}
