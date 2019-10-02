<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

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
