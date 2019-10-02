<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Oxwall\Repositories;

use Iola\Api\Contract\Integration\CommentRepositoryInterface;
use Iola\Api\Entities\Comment;

class CommentRepository implements CommentRepositoryInterface
{
    protected $entityTypeAlias = [
        "photo_comments" => Comment::ENTITY_TYPE_PHOTO,
    ];

    /**
     * @var \BOL_CommentService
     */
    protected $commentService;

    /**
     * @var \BOL_CommentEntityDao
     */
    protected $commentEntityDao;

    public function __construct()
    {
        $this->commentService = \BOL_CommentService::getInstance();
        $this->commentEntityDao = \BOL_CommentEntityDao::getInstance();
    }

    public function findByIds($ids)
    {
        $items = $this->commentService->findCommentListByIds($ids);
        $out = [];
        $entityIds = [];
        foreach ($items as $item) {
            $comment = new Comment();
            $comment->id = (int) $item->id;
            $comment->text = $item->message;
            $comment->createdAt = new \DateTime("@" . $item->createStamp);
            $comment->userId = (int) $item->userId;
            $comment->entityId = (int) $item->commentEntityId; // entityId from related table (ow_base_comment_entity)

            $image = json_decode($item->attachment);
            $image = $image && isset($image->url) ? $image->url : null;
            $comment->image = $image;

            $out[$comment->id] = $comment;

            if (!in_array($item->commentEntityId, $entityIds)) {
                $entityIds[] = $item->commentEntityId;
            }
        }

        $entities = $this->commentEntityDao->findByIdList($entityIds);
        $entitiesMap= [];
        foreach ($entities as $entity) {
            $entitiesMap[$entity->id] = $entity;
        }

        foreach ($out as $comment) {
            $entity = $entitiesMap[$comment->entityId];
            $comment->entityId = $entity->entityId; // id from related table (ow_photo, ow_video_clip and etc.)
            $comment->entityType = $this->entityTypeAlias[$entity->entityType];
        }

        return $out;
    }
}
