<?php

namespace Iola\Api\Integration\Events;

use Iola\Api\App\Event;
use Iola\Api\Contract\Integration\Events\SubscriptionEventInterface;

class SubscriptionEvent extends Event implements SubscriptionEventInterface
{
    /**
     * @var mixed
     */
    protected $data;

    public function __construct($name, $data = [])
    {
        parent::__construct($name);

        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getTime()
    {

    }
}
