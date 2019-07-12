<?php

namespace Iola\Api\App;

use Iola\Api\Contract\App\EventInterface;
use League\Event\Event as LeagueEvent;

class Event extends LeagueEvent implements EventInterface
{

}
