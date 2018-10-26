<?php
/**
 * Created by PhpStorm.
 * User: skambalin
 * Date: 19.10.17
 * Time: 16.21
 */

namespace Everywhere\Api\Contract\Integration;

use Everywhere\Api\Contract\App\EventManagerInterface;

interface IntegrationInterface
{
    /**
     * @param EventManagerInterface $eventManager
     */
    public function init(EventManagerInterface $eventManager);

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

    /**
     * @return ChatRepositoryInterface
     */
    public function getChatRepository();

    /**
     * @return SubscriptionRepositoryInterface
     */
    public function getSubscriptionEventsRepository();
}
