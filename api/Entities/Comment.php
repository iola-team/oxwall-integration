<?php
namespace Everywhere\Api\Entities;

class Comment extends AbstractEntity
{
    /**
     * @var string
     */
    public $text;

    /**
     * @var string
     */
    public $image;

    /**
     * @var \DateTime
     */
    public $createdAt;

    /**
     * @var string
     */
    public $userId;
}