<?php

namespace Everywhere\Api\Schema;

use Everywhere\Api\Contract\Schema\ConnectionFactoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderFactoryInterface;
use GraphQL\Executor\Promise\PromiseAdapter;

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
