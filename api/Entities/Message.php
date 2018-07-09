<?php

namespace Everywhere\Api\Entities;

class Message extends AbstractEntity
{
    /**
     * @var string
     */
    public $userId;

    /**
     * @var string
     */
    public $chatId;

    /**
     * @var array
     */
    public $content;

    /**
     * @var \DateTime
     */
    public $createdAt;
}
