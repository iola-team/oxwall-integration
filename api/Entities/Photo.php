<?php

namespace Iola\Api\Entities;


class Photo extends AbstractEntity
{
    /**
     * @var string
     */
    public $caption;

    /**
     * @var string
     */
    public $userId;

    /**
     * @var \DateTime
     */
    public $createdAt;
}
