<?php

namespace Everywhere\Api\Entities;

class Subscription extends AbstractEntity
{
    /**
     * @var string
     */
    public $query;

    /**
     * @var mixed[]
     */
    public $variables;
}
