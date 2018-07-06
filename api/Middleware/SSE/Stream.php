<?php

namespace Everywhere\Api\Middleware\SSE;

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
        if ($this->shouldEnd) {
            return true;
        }

        while (true) {
            if ($this->iterator->valid()) {
                break;
            } else {
                $this->lastEventId = call_user_func($this->tick);

                if ($this->lastEventId !== null) {
                    break;
                }

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
        if ($this->lastEventId) {
            $out = [
                "id: " . $this->lastEventId,
                "event: end",
            ];

            $this->shouldEnd = true;
        } else {
            $current = $this->iterator->current();

            $data = json_encode([
                "type" => "SUBSCRIPTION_DATA",
                "data" => $current
            ]);

            $out = [
                "event: message",
                "data: " . $data
            ];

            $this->iterator->next();
        }

        return implode("\n", $out) . "\n\n";
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
