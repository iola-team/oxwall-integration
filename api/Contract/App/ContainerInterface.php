<?php

namespace Everywhere\Api\Contract\App;

use Psr\Container\ContainerInterface as PsrContainerInterface;
use Everywhere\Api\Contract\Integration\IntegrationInterface;
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
