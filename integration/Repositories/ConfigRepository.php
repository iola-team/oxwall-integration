<?php

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

        return [
            "emailConfirmIsRequired" => (boolean) $this->owConfig->getValue("base", "confirm_email"),
            "userApproveIsRequired" => (boolean) $this->owConfig->getValue("base", "mandatory_user_approve"),
            "backgroundUrl" => (string) $this->service->getFileUrl("backgroundUrl"),
            "logoUrl" => (string) $this->service->getFileUrl("logoUrl"),
            "primaryColor" => (string) empty($configs["primaryColor"]) ? "#5259FF" : $configs["primaryColor"],
        ];
    }
}
