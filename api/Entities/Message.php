<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

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
