<?php

namespace Everywhere\Api\App\Events;

use Everywhere\Api\Contract\Schema\ViewerInterface;

class AfterRequestEvent extends AbstractRequestEvent
{
    const EVENT_NAME = "core.onAfterRequest";

    public function __construct(ViewerInterface $viewer)
    {
        parent::__construct(self::EVENT_NAME, $viewer);
    }
}
