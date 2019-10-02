<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Contract\Schema\AbstractTypeResolverInterface;
use Iola\Api\Contract\Schema\ContextInterface;
use GraphQL\Executor\Values;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;

class PresentationAwareTypeResolver implements AbstractTypeResolverInterface
{
    /**
     * @param $root
     * @param ContextInterface $context
     * @param ResolveInfo $info
     *
     * @return Type
     */
    public function resolveType($root, ContextInterface $context, ResolveInfo $info)
    {
        $presentationDirective = $info->schema->getDirective("presentation");
        $types = $info->returnType->getTypes();

        foreach ($types as $type) {
            $directiveValue = Values::getDirectiveValues($presentationDirective, $type->astNode);

            if (empty($directiveValue)) {
                continue;
            }

            if (in_array($root["presentation"], $directiveValue["list"])) {
                return $type;
            }
        }

        return null;
    }
}
