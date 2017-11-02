<?php
namespace GraphiQLMiddleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class GraphiQLMiddleware {
    private $path = "";

    public function __construct($path = "/graphiql")
    {
        $this->path = $path;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null) {
        if ($request->getUri()->getPath() === $this->path) {
            echo file_get_contents(__DIR__ . "/graphiql/index.html");
            exit;
        }

        if (!$next) {
            return $response;
        }

        return $next($request, $response);
    }
}
