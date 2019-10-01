<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api;

use Iola\Api\App\App;
use Iola\Api\Contract\Integration\IntegrationInterface;
use Iola\Api\Contract\ServerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class Server implements ServerInterface
{
    protected $baseUrl;

    /**
     * @var IntegrationInterface
     */
    protected $integration;

    protected $app;

    public function __construct($baseUrl, IntegrationInterface $integration)
    {
        $this->baseUrl = $baseUrl;
        $this->integration = $integration;
    }

    public function init() {
        if ($this->app) {
            return;
        }

        $app = new App(
            $this->integration,
            require __DIR__ . '/dependencies.php',
            require __DIR__ . '/configs.php'
        );

        require __DIR__ . '/routes.php';

        /**
         * TODO: Think of moving init phase somewhere where viewer id is already known.
         */
        require __DIR__ . '/init.php';

        $this->app = $app;
    }

    public function run($path)
    {
        $this->init();

        /**
         * @var App
         */
        $app = $this->app;

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
