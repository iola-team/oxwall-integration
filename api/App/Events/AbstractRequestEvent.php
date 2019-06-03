<?php

namespace Everywhere\Api\App\Events;

use Everywhere\Api\App\Event;
use Everywhere\Api\Contract\Schema\ViewerInterface;

abstract class AbstractRequestEvent extends Event
{
    /**
     * @var ViewerInterface
     */
    protected $viewer;

    public function __construct($eventName, ViewerInterface $viewer)
    {
        parent::__construct($eventName);

        $this->viewer = $viewer;
    }

    /**
     * @return ViewerInterface
     */
    public function getViewer()
    {
        return $this->viewer;
    }
}
