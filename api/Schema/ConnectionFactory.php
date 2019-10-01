<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Schema;

use Iola\Api\Contract\Schema\ConnectionFactoryInterface;
use Iola\Api\Contract\Schema\DataLoaderFactoryInterface;

class ConnectionFactory implements ConnectionFactoryInterface
{
    /**
     * @var DataLoaderFactoryInterface
     */
    protected $loaderFactory;

    public function __construct(DataLoaderFactoryInterface $loaderFactory)
    {
        $this->loaderFactory = $loaderFactory;
    }

    public function create($root, $arguments, callable $itemsGetter = null, callable $countGetter = null)
    {
        $getters = [
            "count" => $countGetter,
            "items" => $itemsGetter
        ];

        $loader = $this->loaderFactory->create(function($keys, $arguments) use($getters) {
            $out = [];
            foreach ($keys as $key) {
                $out[$key] = $getters[$key]($arguments);
            }

            return $out;
        });

        $cachedItemsGetter = $getters["items"] ? function($arguments) use($loader) {
            return $loader->load("items", $arguments);
        } : null;

        $cachedCountGetter = $getters["count"] ? function($arguments) use($loader) {
            return $loader->load("count", $arguments);
        } : null;

        $connection = new ConnectionObject($root, $arguments, $cachedItemsGetter, $cachedCountGetter);

        return $connection;
    }
}
