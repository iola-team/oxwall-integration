<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Schema\ScalarTypeResolverInterface;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\StringValueNode;

class CursorResolver implements ScalarTypeResolverInterface
{
    public function serialize($value)
    {
        return json_encode($value);// base64_encode(serialize($value));
    }

    public function parseValue($value)
    {
        if (!is_string($value)) {
            throw new InvariantViolation(
                "Cursor cannot represent non string value: " . Utils::printSafe($value)
            );
        }

        return json_decode($value, true); //unserialize(base64_decode($value));
    }

    public function parseLiteral($ast)
    {
        if ($ast instanceof StringValueNode) {
            return json_decode($ast->value, true); //unserialize(base64_decode($ast->value));
        }

        return null;
    }
}
