<?php

namespace Everywhere\Api\App;

use Everywhere\Api\Contract\App\EventManagerInterface;
use League\Event\Emitter;

class EventManager extends Emitter implements EventManagerInterface
{

}
