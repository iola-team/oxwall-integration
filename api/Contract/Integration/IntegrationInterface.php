<?php
/**
 * Created by PhpStorm.
 * User: skambalin
 * Date: 19.10.17
 * Time: 16.21
 */

namespace Everywhere\Api\Contract\Integration;

interface IntegrationInterface
{
    /**
     * @return UserRepositoryInterface
     */
    public function getUserRepository();

    /**
     * @return PhotoRepositoryInterface
     */
    public function getPhotoRepository();

    /**
     * @return CommentRepositoryInterface
     */
    public function getCommentRepository();

    /**
     * @return AvatarRepositoryInterface
     */
    public function getAvatarRepository();

    /**
     * @return ProfileRepositoryInterface
     */
    public function getProfileRepository();
}
