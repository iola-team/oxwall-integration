<?php

namespace Iola\Api\Contract\Schema;

use GraphQL\Executor\Promise\Promise;

interface DataLoaderInterface
{
    /**
     * @param $key
     * @param array $args
     *
     * @return Promise
     */
    public function load($key, array $args = []);

    /**
     * @param $keys
     * @param array $args
     *
     * @return Promise
     */
    public function loadMany($keys, array $args = []);

    /**
     * @param $key
     * @param array $args
     *
     * @return DataLoaderInterface
     */
    public function clear($key, array $args = []);

    /**
     * @return DataLoaderInterface
     */
    public function clearAll();

    /**
     * @param $key
     * @param $value
     * @param array $args
     *
     * @return DataLoaderInterface
     */
    public function prime($key, $value, array $args = []);
}
