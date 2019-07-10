<?php

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