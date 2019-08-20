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
        $backgroundUrl = $this->service->getFileUrl("backgroundUrl");
        $logoUrl = $this->service->getFileUrl("logoUrl");
        $configs = $this->service->getConfigs();
        $primaryColor = $configs["primaryColor"];

        return [
            "emailConfirmIsRequired" => (boolean) $this->owConfig->getValue("base", "confirm_email"),
            "userApproveIsRequired" => (boolean) $this->owConfig->getValue("base", "mandatory_user_approve"),
            "backgroundUrl" => empty($backgroundUrl) ? null : $backgroundUrl,
            "logoUrl" => empty($logoUrl) ? null : $logoUrl,
            "primaryColor" => empty($primaryColor) ? "5259FF" : $primaryColor,
        ];
    }
}
