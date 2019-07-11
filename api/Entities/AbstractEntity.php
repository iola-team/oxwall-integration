<?php

namespace Iola\Api\Entities;

use Iola\Api\Contract\Entities\EntityInterface;

class AbstractEntity implements EntityInterface
{
    /**
     * @var int
     */
    public $id;

    public function __construct($id = null)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}
