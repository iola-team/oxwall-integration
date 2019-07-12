<?php

namespace Iola\Api\App;

use Iola\Api\Contract\App\ContainerInterface;
use Iola\Api\Contract\Integration\IntegrationInterface;

class Container extends \Slim\Container implements ContainerInterface
{
    public function __construct(IntegrationInterface $integration, array $dependencies, array $settings)
    {
        parent::__construct(array_merge($dependencies, [
            "settings" => $settings,
            IntegrationInterface::class => $integration
        ]));
    }

    public function getIntegration()
    {
        return $this->get(IntegrationInterface::class);
    }

    public function getSettings()
    {
        return $this->get("settings");
    }
}