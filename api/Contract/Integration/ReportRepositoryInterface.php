<?php

namespace Iola\Api\Contract\Integration;

interface ReportRepositoryInterface
{
    const CONTENT_USER = "User";
    const CONTENT_PHOTO = "Photo";

    const REASON_SPAM = "SPAM";
    const REASON_OFFENCE = "OFFENCE";
    const REASON_ILLEGAL = "ILLEGAL";

    /**
     * @param string $contentType
     * @param mixed $contentId
     * @param mixed $userId
     * @param string $reportReason
     * 
     * @return boolean
     */
    public function addReport($contentType, $contentId, $userId, $reportReason);
}