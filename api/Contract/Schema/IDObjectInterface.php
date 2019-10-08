<?php

namespace Iola\Api\Contract\Schema;
use JsonSerializable;

interface IDObjectInterface extends JsonSerializable
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
