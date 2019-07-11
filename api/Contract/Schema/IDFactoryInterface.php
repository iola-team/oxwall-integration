<?php

namespace Iola\Api\Contract\Schema;

interface IDFactoryInterface
{
    /**
     * @param string $typeName
     * @param string|int $id
     * @return IDObjectInterface
     */
    public function create($typeName, $id);

    /**
     * @param string $globalId
     * @return IDObjectInterface
     */
    public function createFromGlobalId($globalId);
}
