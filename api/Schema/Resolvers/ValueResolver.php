<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

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
