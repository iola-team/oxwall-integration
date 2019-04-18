<?php

namespace Everywhere\Api\Contract\Integration;

use Everywhere\Api\Contract\App\EventManagerInterface;
use Everywhere\Api\Contract\Schema\ViewerInterface;

interface IntegrationInterface
{
    /**
     * @param EventManagerInterface $eventManager
     */
    public function init(EventManagerInterface $eventManager, ViewerInterface $viewer);

    /**
     * @return ConfigRepositoryInterface
     */
    public function getConfigRepository();

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
     * @return FriendshipRepositoryInterface
     */
    public function getFriendshipRepository();

    /**
     * @return SubscriptionRepositoryInterface
     */
    public function getSubscriptionEventsRepository();
}
