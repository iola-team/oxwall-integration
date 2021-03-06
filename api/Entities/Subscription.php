<?php

namespace Iola\Api\Entities;

class Subscription extends AbstractEntity
{
    /**
     * @var string
     */
    public $streamId;

    /**
     * @var string
     */
    public $query;

    /**
     * @var mixed[]
     */
    public $variables;
}
