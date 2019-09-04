<?php

namespace Iola\Api\Auth;

class Identity
{
    /**
     * @var string
     */
    public $userId;

    /**
     * @var int
     */
    public $issueTime;

    /**
     * @var int
     */
    public $expirationTime;
}