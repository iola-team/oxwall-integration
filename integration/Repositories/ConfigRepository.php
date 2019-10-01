<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Oxwall\Repositories;

use Iola\Api\Contract\Integration\ConfigRepositoryInterface;
use OW;
use OW_Config;
use IOLA_BOL_Service;

class ConfigRepository implements ConfigRepositoryInterface
{
    /**
     * @var $owConfig OW_Config
     */
    protected $owConfig;

    /**
     * @var IOLA_BOL_Service
     */
    protected $service;

    public function __construct()
    {
        $this->owConfig = OW::getConfig();
        $this->service = IOLA_BOL_Service::getInstance();
    }

    public function getAll($args)
    {
        $configs = $this->service->getConfigs();
        $backgroundUrl = $this->service->getFileUrl("backgroundUrl");
        $logoUrl = $this->service->getFileUrl("logoUrl");

        return [
            "emailConfirmIsRequired" => (boolean) $this->owConfig->getValue("base", "confirm_email"),
            "userApproveIsRequired" => (boolean) $this->owConfig->getValue("base", "mandatory_user_approve"),
            "backgroundUrl" => empty($backgroundUrl) ? null : $backgroundUrl,
            "logoUrl" => empty($logoUrl) ? null : $logoUrl,
            "primaryColor" => empty($configs["primaryColor"]) ? "#5259FF" : $configs["primaryColor"],
        ];
    }
}
