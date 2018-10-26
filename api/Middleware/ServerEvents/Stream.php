<?php

namespace Everywhere\Api\Middleware\ServerEvents;

use Psr\Http\Message\StreamInterface;

class Stream implements EventStreamInterface
{
    /**
     * @var $iterator
     */
    protected $iterator;

    /**
     * @var callable
     */
    protected $tick;
    protected $lastEventId;
    protected $shouldEnd = false;
    protected $started = false;

    protected $keepAliveInterval = 5;

    public function __construct(\Iterator $iterator, callable $tick)
    {
        $this->tick = $tick;
        $this->iterator = $iterator;
        $this->iterator->rewind();
    }

    public function __toString()
    {
        return "";
    }

    public function close()
    {

    }

    public function detach()
    {

    }

    public function getSize()
    {
        return null;
    }

    public function tell()
    {

    }

    public function eof()
    {
        if (!$this->started) {
            return false;
        }

        if ($this->shouldEnd) {
            return true;
        }

        $lastEventId = null;
        $keepAliveTime = time() + $this->keepAliveInterval;

        while (true) {
            $iterationTime = time();

            if ($this->iterator->valid()) {
                break;
            } else if($keepAliveTime <= $iterationTime) {
                $keepAliveTime = $iterationTime + $this->keepAliveInterval;

                break;
            } else if ($lastEventId) {
                $this->lastEventId = $lastEventId;

                break;
            } else {
                $lastEventId = call_user_func($this->tick);
                $this->iterator->rewind();
            }
        }

        return false;
    }

    public function isSeekable()
    {
        return false;
    }

    public function seek($offset, $whence = SEEK_SET)
    {

    }

    public function rewind()
    {

    }

    public function isWritable()
    {
        return false;
    }

    public function write($string)
    {

    }

    public function isReadable()
    {
        return true;
    }

    public function read($length)
    {
        if (!$this->started) {
            $this->started = true;
            $out = [
                "event: start",
                "retry" => 1000
            ];
        } else if ($this->lastEventId) {
            $this->shouldEnd = true;
            $out = [
                "id: " . $this->lastEventId,
                "event: end",
            ];
        } else if ($this->iterator->valid()) {
            $current = $this->iterator->current();

            $data = json_encode([
                "type" => "SUBSCRIPTION_DATA",
                "data" => $current["data"],
                "subscriptionId" => $current["key"],
            ]);

            $out = [
                "event: message",
                "data: " . $data
            ];

            $this->iterator->next();
        } else {
            $data = json_encode([
                "type" => "KEEPALIVE"
            ]);

            $out = [
                "event: message",
                "data: " . $data
            ];
        }

        return implode("\n", $out) . ($this->shouldEnd ? "\n" : "\n\n");
    }

    public function getContents()
    {
        return null;
    }

    public function getMetadata($key = null)
    {
        return null;
    }
}
