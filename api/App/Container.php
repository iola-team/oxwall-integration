<?php
/**
 * Created by PhpStorm.
 * User: skambalin
 * Date: 19.10.17
 * Time: 18.21
 */

namespace Everywhere\Api\App;

use Everywhere\Api\Contract\App\ContainerInterface;
use Everywhere\Api\Contract\Integration\IntegrationInterface;

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