<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Schema\ScalarTypeResolverInterface;
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
