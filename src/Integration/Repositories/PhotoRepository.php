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

    public function __construct()
    {
        $this->photoService = \PHOTO_BOL_PhotoService::getInstance();
        $this->tempPhotoService = \PHOTO_BOL_PhotoTemporaryService::getInstance();
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

    public function addUserPhoto($userId, array $input)
    {
        /**
         * @var $file UploadedFileInterface
         */
        $file = $input["file"];

        $tmpFileName = Integration::getTmpDir() . uniqid('photo-') . $file->getClientFilename();
        $file->moveTo($tmpFileName);

        $tempPhotoId = $this->tempPhotoService->addTemporaryPhoto($tmpFileName, $userId);
        $albumId = $this->getAlbumId($userId);
        $photoDto = $this->tempPhotoService->moveTemporaryPhoto($tempPhotoId, $albumId, '');

        return $photoDto->id;
    }

    public function deleteByIds($ids)
    {
        foreach ($ids as $id) {
            $this->photoService->deletePhoto($id);
        }
    }
}
