<?php
/**
 * Created by PhpStorm.
 * User: skambalin
 * Date: 21.10.17
 * Time: 17.29
 */

namespace Everywhere\Api\Entities;


class Photo extends AbstractEntity
{
    /**
     * @var string
     */
    public $url;

    /**
     * @var int
     */
    public $userId;

    /**
     * @var int
     */
    public $createdAt;
}
