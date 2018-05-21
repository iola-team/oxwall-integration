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
     * @var int
     */
    public $activityTime;

    /**
     * @var mixed
     */
    public $accountTypeId;
}
