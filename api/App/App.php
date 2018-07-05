<?php
/**
 * Created by PhpStorm.
 * User: skambalin
 * Date: 19.10.17
 * Time: 18.21
 */

namespace Everywhere\Api\App;

use Everywhere\Api\Contract\Integration\IntegrationInterface;

class App extends \Slim\App
{
    public function __construct(IntegrationInterface $integration, array $dependencies, array $settings)
    {
        $container = new Container($integration, $dependencies, $settings);

        parent::__construct($container);
    }
}
