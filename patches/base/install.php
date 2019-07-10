<?php

$dbPrefix = OW_DB_PREFIX;
$sql = [
    /**
     * Set UTF8 charset to comment content column
     */
    "ALTER TABLE `{$dbPrefix}base_comment` MODIFY `message` text CHARACTER SET utf8mb4 NOT NULL;",

    /**
     * Set UTF8 charset to comment attachement column
     */
    "ALTER TABLE `{$dbPrefix}base_comment` MODIFY `attachment` text CHARACTER SET utf8mb4;",

    /**
     * Set UTF8 charset to question text data column
     */
    "ALTER TABLE `{$dbPrefix}base_question_data` MODIFY `textValue` text CHARACTER SET utf8mb4 NOT NULL;"
];

foreach ( $sql as $query ) {
    try {
        OW::getDbo()->query($query);
    } catch ( Exception $e ) {
        // Skip...
    }
}