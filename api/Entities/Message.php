<?php

namespace Iola\Api\Entities;

class Message extends AbstractEntity
{
    const STATUS_READ = "READ";
    const STATUS_DELIVERED = "DELIVERED";

    /**
     * @var string
     */
    public $userId;

    /**
     * @var string
     */
    public $chatId;

    /**
     * The status might be "DELIVERED" or "READ"
     *
     * DELIVERED - default status which means that the message is stored in DB
     * READ - indicates that the message has been read by any recipient
     *
     * @var string
     */
    public $status;

    /**
     * @var array
     */
    public $content;

    /**
     * @var \DateTime
     */
    public $createdAt;
}
