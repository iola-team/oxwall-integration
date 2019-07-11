<?php

namespace Iola\Oxwall;

use UTIL_String;
use OW_Route;

class ServerRoute extends OW_Route
{
    const PATH_ATTR = "path";
    const ROUTE_NAME = "iola.api";

    public function __construct($baseUrl)
    {
        parent::__construct(self::ROUTE_NAME, $baseUrl, ServerController::class, 'index');
    }

    public function match($uri) {
        $uri = strtoupper(
            UTIL_String::removeFirstAndLastSlashes(
                trim($uri)
            )
        );

        $uriParts = explode("/", $uri);
        $baseUrl = strtoupper($this->getRoutePath());
        $baseUrlParts = explode("/", $baseUrl);
        $pathParts = array_slice($uriParts, count($baseUrlParts));
        $dispatchAttrs = $this->getDispatchAttrs();

        $this->setDispatchAttrs(array_merge($dispatchAttrs, [
            self::DISPATCH_ATTRS_VARLIST => [
                self::PATH_ATTR => "/" . strtolower(implode('/', $pathParts))
            ]
        ]));

        return array_slice($uriParts, 0, count($baseUrlParts)) == $baseUrlParts;
    }
}