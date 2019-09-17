<?php

/**
 * Update languages
 */
Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . "langs.zip", "iola");