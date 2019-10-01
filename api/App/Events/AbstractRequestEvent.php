<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\App\Events;

use Iola\Api\App\Event;
use Iola\Api\Contract\Schema\ViewerInterface;

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
