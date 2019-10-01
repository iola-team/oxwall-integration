<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

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