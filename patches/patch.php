<?php

/**
 * Patching is a very dirty practice and should be avoided when possible.
 *
 * All patches should be removed eventually.
 * To be able to remove a patch, changes from patch should be applied to original Oxwall class via merge request.
 */

require_once __DIR__ . "/MAILBOX_BOL_ConversationService.php";
require_once __DIR__ . "/BOL_EmailVerifyService.php";
