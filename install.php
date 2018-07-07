<?php

$sql = [
    "CREATE TABLE `{OW_DB_PREFIX}esapi_subscription_event` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(100) NOT NULL,
      `timeOffset` int(11) NULL,
      `data` text NOT NULL,
      PRIMARY KEY (`id`),
      KEY `createdAt` (`createdAt`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;",

    "CREATE TABLE `{OW_DB_PREFIX}esapi_subscription` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `query` text NOT NULL,
      `variables` text NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8"
];

foreach ( $sql as $query )
{
    try
    {
        OW::getDbo()->query($query);
    }
    catch ( Exception $e )
    {
        //Log
    }
}
