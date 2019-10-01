<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

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