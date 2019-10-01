<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

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
