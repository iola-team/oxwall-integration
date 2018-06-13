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
     * @var string
     */
    public $content;
}
