<?php

namespace Iola\Api\Contract\Schema;


interface DataLoaderFactoryInterface
{
    /**
     * @param callable $source
     * @param null $emptyValue
     *
     * @return DataLoaderInterface
     */
    public function create(callable $source, $emptyValue = null);
}