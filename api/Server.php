<?php

namespace Everywhere\Api;

use Everywhere\Api\App\App;
use Everywhere\Api\Contract\App\EventManagerInterface;
use Everywhere\Api\Contract\Integration\IntegrationInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class Server
{
    protected $baseUrl;

    /**
     * @var IntegrationInterface
     */
    protected $integration;

    public function __construct($baseUrl, IntegrationInterface $integration)
    {
        /**
         * There are a lot of nesting invocation in `graphql` schema parsing.
         * So, have to increase this `xdebug` config.
         */
        ini_set('xdebug.max_nesting_level', 200);

        $this->baseUrl = $baseUrl;
        $this->integration = $integration;
    }

    private function init() {
        $app = new App(
            $this->integration,
            require __DIR__ . '/dependencies.php',
            require __DIR__ . '/configs.php'
        );

        require __DIR__ . '/routes.php';
        require __DIR__ . '/init.php';

        return $app;
    }

    public function run($path) {

        /**
         * @var App
         */
        $app = $this->init();

        /**
         * @var ResponseInterface $response
         */
        $response = $app->getContainer()->get("response");

        /**
         * @var ServerRequestInterface $request
         */
        $request = $app->getContainer()->get("request");

        /**
         * @var UriInterface $uri
         */
        $uri = $request->getUri()->withPath($path);
        $request = $request->withUri($uri->withBasePath($this->baseUrl));
        $response = $app->process($request, $response);

        $app->respond($response);
    }
}
