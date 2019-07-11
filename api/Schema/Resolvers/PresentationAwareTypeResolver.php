<?php

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
