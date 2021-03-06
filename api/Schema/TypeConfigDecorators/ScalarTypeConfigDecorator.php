<?php

namespace Iola\Api\Schema\TypeConfigDecorators;

use Iola\Api\Contract\Schema\Types\ScalarTypeInterface;
use Iola\Api\Schema\AbstractTypeConfigDecorator as TypeConfigDecorator;
use GraphQL\Language\AST\NodeKind;

class ScalarTypeConfigDecorator extends TypeConfigDecorator
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
        if ($this->getKind($typeConfig) !== NodeKind::SCALAR_TYPE_DEFINITION) {
            return $typeConfig;
        }

        if (!array_key_exists($typeConfig["name"], $this->typesMap)) {
            return $typeConfig;
        }

        /**
         * @var $typeDecorator ScalarTypeInterface
         */
        $typeDecorator = call_user_func($this->resolve, $this->typesMap[$typeConfig["name"]]);

        $typeConfig["serialize"] = function($value) use ($typeDecorator) {
            return $typeDecorator->serialize($value);
        };

        $typeConfig["parseValue"] = function($value) use ($typeDecorator) {
            return $typeDecorator->parseValue($value);
        };

        $typeConfig["parseLiteral"] = function($ast) use ($typeDecorator) {
            return $typeDecorator->parseLiteral($ast);
        };

        return $typeConfig;
    }
}
