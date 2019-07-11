<?php

namespace Iola\Api\App;

use Iola\Api\Contract\Integration\IntegrationInterface;

class App extends \Slim\App
{
    public function __construct(IntegrationInterface $integration, array $dependencies, array $settings)
    {
        $container = new Container($integration, $dependencies, $settings);

        parent::__construct($container);
    }
}
