<?php

namespace Iola\Api\Contract\Integration;

use Iola\Api\Contract\App\EventManagerInterface;

interface IntegrationInterface
{
    /**
     * @param EventManagerInterface $eventManager
     */
    public function init(EventManagerInterface $eventManager);

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

    /**
     * @return ReportRepositoryInterface
     */
    public function getReportRepository();

    /**
     * @return BlockRepositoryInterface
     */
    public function getBlockRepository();
}
