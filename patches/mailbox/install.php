<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

$dbPrefix = OW_DB_PREFIX;
$sql = [
    /**
     * Set UTF8 charset to messages content column
     */
    "ALTER TABLE `{$dbPrefix}mailbox_message` MODIFY `text` mediumtext CHARACTER SET utf8mb4 NOT NULL;"
];

foreach ( $sql as $query ) {
    try {
        OW::getDbo()->query($query);
    } catch ( Exception $e ) {
        // Skip...
    }
}