<?php
/**
 * Created by PhpStorm.
 * User: skambalin
 * Date: 19.10.17
 * Time: 18.05
 */

namespace Everywhere\Api\Entities;


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
     * @var boolean
     */
    public $isEmailVerified;

    /**
     * @var mixed
     */
    public $accountTypeId;
}
