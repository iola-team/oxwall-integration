<?php

namespace Iola\Api\Contract\App;

use Psr\Container\ContainerInterface as PsrContainerInterface;
use Iola\Api\Contract\Integration\IntegrationInterface;
use Slim\Collection;

interface ContainerInterface extends PsrContainerInterface
{
    /**
     * @return IntegrationInterface
     */
    public function getIntegration();

    /**
     * @return Collection
     */
    public function getSettings();
}
