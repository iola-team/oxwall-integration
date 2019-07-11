<?php

namespace Iola\Api\Schema;

use Iola\Api\Contract\Schema\IDFactoryInterface;

class IDFactory implements IDFactoryInterface
{
    public function create($typeName, $id)
    {
        return new IDObject($typeName, $id, function($typeName, $id) {
            return "$typeName:$id";
        });
    }

    public function createFromGlobalId($globalId)
    {
        $idParts = explode(":", $globalId);
        list($typeName, $id) = count($idParts) === 1
            ? [null, $idParts[0]]
            : $idParts;

        return $this->create($typeName, $id);
    }
}
