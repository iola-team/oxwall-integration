<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

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
