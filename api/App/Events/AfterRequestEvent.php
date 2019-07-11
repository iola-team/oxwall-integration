<?php

namespace Iola\Api\App\Events;

use Iola\Api\Contract\Schema\ViewerInterface;

class AfterRequestEvent extends AbstractRequestEvent
{
    const EVENT_NAME = "core.onAfterRequest";

    public function __construct(ViewerInterface $viewer)
    {
        parent::__construct(self::EVENT_NAME, $viewer);
    }
}
