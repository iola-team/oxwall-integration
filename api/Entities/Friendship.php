<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

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
