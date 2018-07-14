<?php

namespace Everywhere\Api\App;

use Everywhere\Api\Contract\App\EventInterface;
use League\Event\Event as LeagueEvent;

class Event extends LeagueEvent implements EventInterface
{

}
