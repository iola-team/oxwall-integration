<?php

namespace Iola\Api\Contract\Integration;

use Iola\Api\Entities\Photo;
use Iola\Api\Entities\Comment;

interface PhotoRepositoryInterface
{
    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids);

    /**
     * @param $ids
     * @param array $args
     * @return string[]
     */
    public function getUrls($ids, array $args);

    /**
     * @param $ids
     * @return void
     */
    public function deleteByIds($ids);

    /**
     * @param $ids
     * @param array $args
     * @return mixed
     */
    public function findComments($ids, array $args);

    /**
     * @param string[] $photoIds
     *
     * @return mixed[]
     */
    public function findCommentsParticipantIds($photoIds);

    /**
     * @param $ids
     * @param array $args
     * @return int[]
     */
    public function countComments($ids, array $args);

    /**
     *
     * @param $userId
     * @param array $input
     * @return Comment
     */
    public function addComment($userId, array $input);

    /**
     *
     * @param $userId
     * @param array $input
     * @return Photo
     */
    public function addUserPhoto($userId, array $input);
}
