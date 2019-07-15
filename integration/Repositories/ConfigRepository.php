<?php

namespace Iola\Oxwall\Repositories;

use Iola\Api\Contract\Integration\ConfigRepositoryInterface;
use OW;
use OW_Config;

class ConfigRepository implements ConfigRepositoryInterface
{
    /**
     * @var $owConfig OW_Config
     */
    protected $owConfig;

    public function __construct()
    {
        $this->owConfig = OW::getConfig();
    }

    public function getAll($args)
    {
        return [
            "emailConfirmIsRequired" => (boolean) $this->owConfig->getValue("base", "confirm_email"),
            "userApproveIsRequired" => (boolean) $this->owConfig->getValue("base", "mandatory_user_approve")
        ];
    }
}