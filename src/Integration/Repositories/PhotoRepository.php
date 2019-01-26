<?php

namespace Everywhere\Oxwall\Integration\Repositories;

use Everywhere\Api\Contract\Integration\PhotoRepositoryInterface;
use Everywhere\Api\Entities\Photo;
use Everywhere\Oxwall\Integration\Integration;
use Psr\Http\Message\UploadedFileInterface;

class PhotoRepository implements PhotoRepositoryInterface
{
    /**
     * @var \PHOTO_BOL_PhotoService
     */
    private $photoService;

    /**
     * @var \PHOTO_BOL_PhotoTemporaryService
     */
    private $tempPhotoService;

    /**
     * @var \BOL_CommentService
     */
    protected $commentService;

    protected $commentEntityType = "photo_comments";

    /**
     * @var \OW_EventManager
     */
    protected $eventManager;

    public function __construct()
    {
        $this->photoService = \PHOTO_BOL_PhotoService::getInstance();
        $this->tempPhotoService = \PHOTO_BOL_PhotoTemporaryService::getInstance();
        $this->commentService = \BOL_CommentService::getInstance();
        $this->commentDao = \BOL_CommentDao::getInstance();
        $this->eventManager = \OW::getEventManager();
    }

    /**
     * Creates or gets an album for mobile uploads
     * TODO: Make the name configurable
     *
     * @param int $userId
     * @return int
     */
    private function getAlbumId($userId)
    {
        $albumName = "Mobile uploads";
        $album = \OW::getEventManager()->call("photo.album_find", array(
            "userId" => $userId,
            "albumTitle" => $albumName
        ));

        if (empty($album)) {
            $data = \OW::getEventManager()->call("photo.album_add", array(
                "userId" => $userId,
                "name" => $albumName
            ));

            $albumId = $data["albumId"];
        } else {
            $albumId = $album["id"];
        }

        return $albumId;
    }

    public function findByIds($ids)
    {
        $items = $this->photoService->findPhotoListByIdList($ids, 1, count($ids));
        $out = [];

        foreach ($items as $item) {
            $photo = new Photo($item["id"]);
            $photo->url = $item["url"];
            $photo->caption = $item["description"];
            $photo->userId = $item["userId"];
            $createdAt = $item["addDatetime"];
            $photo->createdAt = new \DateTime("@$createdAt");

            $out[$photo->id] = $photo;
        }

        return $out;
    }

    public function deleteByIds($ids)
    {
        foreach ($ids as $id) {
            $this->photoService->deletePhoto($id);
        }
    }

    public function findComments($photoIds, array $args)
    {
        $out = [];
        foreach($photoIds as $photoId) {
            $page = $args["offset"] ? floor($args["offset"] / ($args["count"] - 1)) + 1 : 1;

            /**
             * @var $commentDtos \BOL_Comment[]
             */
            $commentDtos = $this->commentService->findCommentList(
                (int)$this->commentEntityType,
                (int)$photoId,
                $page,
                $args["count"]
            );

            foreach ($commentDtos as $commentDto) {
                if (empty($out[(int)$photoId])) {
                    $out[(int)$photoId] = [$commentDto->getId()];
                } else {
                    $out[(int)$photoId][] = $commentDto->getId();
                }
            }
        }

        return $out;
    }

    // @TODO: Use this method for mentions subscription (photo comments)
    public function findCommentsParticipantIds($photoIds)
    {
        $out = [];
        foreach ($photoIds as $id) {
            // @TODO: commentService doesn't have the method "get photo comments participants" (rewrite the foreach+in_array to raw SQL?)
            $commentsDto = $this->commentService->findFullCommentList($this->commentEntityType, $id);
            $out[$id] = [];

            foreach ($commentsDto as $commentDto) {
                $userId = $commentDto->getUserId();

                if (!in_array($userId, $out[$id])) {
                    $out[$id][] = $userId;
                }
            }
        }

        return $out;
    }

    public function countComments($photoIds, array $args)
    {
      $out = [];

      foreach ($photoIds as $id) {
        $out[$id] = $this->commentService->findCommentCount($this->commentEntityType, $id);
      }

      return $out;
    }

    public function addComment($userId, array $input)
    {
        $pluginKey = "photo";
        $attachment = null;
        $entityId = $input["photoId"]->getId();
        $message = $input["text"];

        $commentDto = $this->commentService->addComment($this->commentEntityType, $entityId, $pluginKey, $userId, $message, $attachment);
        $commentId = $commentDto->id;

        $event = new \OW_Event("base_add_comment", [
            "entityType" => $this->commentEntityType,
            "entityId" => $entityId,
            "userId" => $userId,
            "commentId" => $commentId,
            "pluginKey" => $pluginKey,
            "attachment" => $attachment,
        ]);
        \OW::getEventManager()->trigger($event);

        return $commentId;
    }

    public function addUserPhoto($userId, array $input)
    {
        /**
         * @var $file UploadedFileInterface
         */
        $file = $input["file"];

        $tmpFileName = Integration::getTmpDir() . uniqid("photo-") . $file->getClientFilename();
        $file->moveTo($tmpFileName);

        $tempPhotoId = $this->tempPhotoService->addTemporaryPhoto($tmpFileName, $userId);
        $albumId = $this->getAlbumId($userId);
        $photoDto = $this->tempPhotoService->moveTemporaryPhoto($tempPhotoId, $albumId, "");

        return $photoDto->id;
    }
}
