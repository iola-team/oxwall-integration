<?php
/**
 * Created by PhpStorm.
 * User: skambalin
 * Date: 20.10.17
 * Time: 17.31
 */

namespace Everywhere\Api\Entities;

use Everywhere\Api\Contract\Entities\EntityInterface;

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
