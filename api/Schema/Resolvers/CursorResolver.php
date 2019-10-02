<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Contract\Schema\ScalarTypeResolverInterface;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\StringValueNode;

class CursorResolver implements ScalarTypeResolverInterface
{
    public function serialize($value)
    {
        return base64_encode(serialize($value));
    }

    public function parseValue($value)
    {
        if (!is_string($value)) {
            throw new InvariantViolation(
                "Cursor cannot represent non string value: " . Utils::printSafe($value)
            );
        }

        return unserialize(base64_decode($value));
    }

    public function parseLiteral($ast)
    {
        if ($ast instanceof StringValueNode) {
            return unserialize(base64_decode($ast->value));
        }

        return null;
    }
}
