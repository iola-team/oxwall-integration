<?php

namespace Everywhere\Oxwall\Integration\Repositories;

use Everywhere\Api\Contract\Integration\ReportRepositoryInterface;

class ReportRepository implements ReportRepositoryInterface
{
    protected $entityTypes = [
        self::CONTENT_USER => \BASE_CLASS_ContentProvider::ENTITY_TYPE_PROFILE,
        self::CONTENT_PHOTO => \PHOTO_CLASS_ContentProvider::ENTITY_TYPE,
    ];

    protected $reasons = [
        self::REASON_ILLEGAL => "illegal",
        self::REASON_OFFENCE => "offence",
        self::REASON_SPAM => "spam",
    ];

    public function addReport($contentType, $contentId, $userId, $reportReason) {
        \BOL_FlagService::getInstance()->addFlag(
            $this->entityTypes[$contentType],
            $contentId,
            $this->reasons[$reportReason],
            $userId
        );

        return true;
    }
}