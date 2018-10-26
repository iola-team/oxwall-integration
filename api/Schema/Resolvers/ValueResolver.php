<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Schema\ScalarTypeResolverInterface;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\AST\ValueNode;
use GraphQL\Utils\Utils;

class ValueResolver implements ScalarTypeResolverInterface
{

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function serialize($value)
    {
        return $value;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function parseValue($value)
    {
        return $value;
    }

    /**
     * @param \GraphQL\Language\AST\Node $ast
     *
     * @return mixed|void
     */
    public function parseLiteral($ast)
    {
        if ($ast instanceof ValueNode) {
            return $ast->value;
        }

        return null;
    }
}
