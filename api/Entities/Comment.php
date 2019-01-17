<?php
namespace Everywhere\Api\Entities;

class Comment extends AbstractEntity
{
    const ENTITY_TYPE_PHOTO = "PHOTO";

    /**
     * @var string
     */
    public $text;

    /**
     * @var \DateTime
     */
    public $createdAt;

    /**
     * @var string
     */
    public $userId;

    /**
     * @var integer
     */
    public $entityId;

    /**
     * @var string
     */
    public $entityType;
}