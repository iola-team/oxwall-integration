<?php

namespace Iola\Api\Contract\Integration;

use Iola\Api\Contract\App\EventManagerInterface;

interface EventSourceInterface extends EventManagerInterface
{
    /**
     * Loads persisted events starting from given milliseconds offset and returns last event milliseconds offset or null
     *
     * @param int|null $timeOffset
     *
     * @return int|null
     */
    public function loadEvents($timeOffset = null);
}
