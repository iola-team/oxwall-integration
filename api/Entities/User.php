<?php

namespace Iola\Api\Entities;


class User extends AbstractEntity
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $email;

    /**
     * @var \DateTime|int
     */
    public $activityTime;

    /**
     * @var mixed
     */
    public $accountTypeId;
}
