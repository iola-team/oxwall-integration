<?php
namespace Everywhere\Api\Entities;

class Comment extends AbstractEntity
{
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
     * Relation with photo via ow_base_comment_entity table
     * @var integer
     */
    public $entityId;

    /**
     * @var integer
     *
     */
    public $photoId;
}