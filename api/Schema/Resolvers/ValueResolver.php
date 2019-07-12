<?php

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Contract\Schema\ScalarTypeResolverInterface;
use GraphQL\Language\AST\ValueNode;
use GraphQL\Language\AST\Node;

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
     * @param Node $ast
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
