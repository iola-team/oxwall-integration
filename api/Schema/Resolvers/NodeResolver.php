<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Contract\Schema\ContextInterface;
use Iola\Api\Contract\Schema\IDObjectInterface;
use Iola\Api\Contract\Schema\AbstractTypeResolverInterface;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use GraphQL\Utils;

class NodeResolver implements AbstractTypeResolverInterface
{
    /**
     * @param IDObjectInterface $root
     * @param ContextInterface $context
     * @param ResolveInfo $info
     *
     * @return Type
     */
    public function resolveType($root, ContextInterface $context, ResolveInfo $info)
    {
        if (!$root instanceof IDObjectInterface) {
            throw new InvariantViolation(
                'Node interface requires `IDObjectInterface` instance as root value, but received: ' . Utils::printSafe($root)
            );
        }

        return $info->schema->getType($root->getType());
    }
}
