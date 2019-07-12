<?php

namespace Iola\Api\Contract\Schema;

interface ConnectionFactoryInterface
{
    /**
     * @param $root
     * @param $arguments
     * @param callable|null $itemsGetter
     * @param callable|null $countGetter
     *
     * @return ConnectionObjectInterface
     */
    public function create($root, $arguments, callable $itemsGetter = null, callable $countGetter = null);
}
