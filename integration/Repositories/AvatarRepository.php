<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Oxwall\Repositories;

use Iola\Api\Contract\Integration\AvatarRepositoryInterface;
use Iola\Api\Entities\Avatar;
use Psr\Http\Message\UploadedFileInterface;

class AvatarRepository implements AvatarRepositoryInterface
{
    /**
     * @var \BOL_AvatarService
     */
    protected $avatarService;

    protected $avatarSizes = [
        "SMALL" => 1,
        "MEDIUM" => 2,
        "BIG" => 3
    ];

    public function __construct()
    {
        $this->avatarService = \BOL_AvatarService::getInstance();
    }

    public function findByIds($ids)
    {
        $avatarDtos = $this->avatarService->findAvatarByIdList($ids);
        $out = [];

        /**
         * @var $avatarDto \BOL_Avatar
         */
        foreach ($avatarDtos as $avatarDto) {
            $avatar = new Avatar($avatarDto->id);
            $avatar->userId = $avatarDto->userId;

            $out[$avatarDto->id] = $avatar;
        }

        return $out;
    }

    public function getUrls($ids, array $args)
    {
        $size = $this->avatarSizes[$args["size"]];
        $avatarDtos = $this->avatarService->findAvatarByIdList($ids);

        /**
         * @var $avatarDto \BOL_Avatar
         */
        foreach ($avatarDtos as $avatarDto) {
            $out[$avatarDto->id] = $this->avatarService->getAvatarUrlByAvatarDto($avatarDto, $size);
        }

        return $out;
    }

    public function addAvatar(array $args)
    {
        /**
         * @var $file UploadedFileInterface
         */
        $file = $args["file"];
        $userId = $args["userId"];

        $tmpAvatarPath = $this->avatarService->getTempAvatarPath(uniqid(), 3);
        $file->moveTo($tmpAvatarPath);

        if (!$this->avatarService->setUserAvatar($userId, $tmpAvatarPath, [])) {
            return null;
        }

        unlink($tmpAvatarPath);
        $avatarDto = $this->avatarService->findByUserId($userId);

        return $avatarDto ? $avatarDto->id : null;
    }

    public function deleteAvatar(array $args)
    {
        return $this->avatarService->deleteAvatarById($args["id"]);
    }
}
