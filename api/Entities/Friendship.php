<?php

namespace Iola\Api\Entities;

class Friendship extends AbstractEntity
{
    const STATUS_IGNORED = "IGNORED";
    const STATUS_PENDING = "PENDING";
    const STATUS_ACTIVE = "ACTIVE";

    /**
     * @var string
     */
    public $userId;

    /**
     * @var string
     */
    public $friendId;

    /**
     * @var string
     */
    public $status;

    /**
     * @var \DateTime
     */
    public $createdAt;
}
