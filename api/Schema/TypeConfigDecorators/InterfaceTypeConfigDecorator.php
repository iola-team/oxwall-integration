<?php

namespace Everywhere\Api\Schema\TypeConfigDecorators;

use Everywhere\Api\Contract\Schema\AbstractTypeResolverInterface;
use Everywhere\Api\Contract\Schema\TypeConfigDecoratorInterface;
use Everywhere\Api\Schema\AbstractTypeConfigDecorator;
use GraphQL\Language\AST\NodeKind;

class InterfaceTypeConfigDecorator extends AbstractTypeConfigDecorator
{
    protected $typesMap;
    protected $resolve;

    public function __construct(array $typesMap, callable $resolve)
    {
        $this->typesMap = $typesMap;
        $this->resolve = $resolve;
    }

    public function decorate(array $typeConfig)
    {
        if ($this->getKind($typeConfig) !== NodeKind::INTERFACE_TYPE_DEFINITION) {
            return $typeConfig;
        }

        if (!array_key_exists($typeConfig["name"], $this->typesMap)) {
            return $typeConfig;
        }

        /**
         * @var $typeDecorator AbstractTypeResolverInterface
         */
        $typeDecorator = call_user_func($this->resolve, $this->typesMap[$typeConfig["name"]]);

        $typeConfig["resolveType"] = function($root, $context, $info) use ($typeDecorator) {
            return $typeDecorator->resolveType($root, $context, $info);
        };

        return $typeConfig;
    }
}
