<?php

namespace Everywhere\Api\Entities;

class SubscriptionEvent extends AbstractEntity
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var mixed
     */
    public $data;

    /**
     * Time offset in milliseconds
     *
     * @var int
     */
    public $timeOffset;
}
