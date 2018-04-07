<?php
/**
 * Created by PhpStorm.
 * User: skambalin
 * Date: 21.10.17
 * Time: 17.22
 */

namespace Everywhere\Oxwall\Integration\Repositories;

use Everywhere\Api\Contract\Integration\PhotoRepositoryInterface;
use Everywhere\Api\Entities\Photo;

class PhotoRepository implements PhotoRepositoryInterface
{
    public function findByIds($ids)
    {
        $items = \PHOTO_BOL_PhotoService::getInstance()->findPhotoListByIdList($ids, 1, count($ids));
        $out = [];

        foreach ($items as $item) {
            $photo = new Photo((int) $item["id"]);
            $photo->url = $item["url"];
            $photo->userId = (int) $item["userId"];

            $out[$photo->id] = $photo;
        }

        return $out;
    }

    public function findComments($photoIds, array $args)
    {
        $entities = array_map(function($photoId) use ($args) {
            return [
                "entityId" => (int)$photoId,
                "entityType" => "photo_comments",
                "countOnPage" => $args["count"],
            ];
        }, $photoIds);
        $items = \BOL_CommentDao::getInstance()->findBatchCommentsList($entities);

        $out = [];
        foreach ($items as $item) {
            $userId = (int) $item->userId;
            $userItems = empty($out[$userId]) ? [] : $out[$userId];
            $userItems[] = $item->id;

            $out[$userId] = $userItems;
        }

        return $out;
    }
}
