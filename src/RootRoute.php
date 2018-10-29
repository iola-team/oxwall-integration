<?php

namespace Everywhere\Oxwall;


class RootRoute extends \OW_Route
{
    const PATH_ATTR = "path";

    public function __construct($routeName, $baseUrl)
    {
        parent::__construct($routeName, $baseUrl, RootController::class, 'index');
    }

    public function match($uri) {
        $uri = strtoupper(
            \UTIL_String::removeFirstAndLastSlashes(
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