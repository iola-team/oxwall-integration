<?php

namespace Everywhere\Api\Schema;

use Everywhere\Api\Contract\Schema\TypeConfigDecoratorInterface;
use GraphQL\Language\AST\Node;

abstract class AbstractTypeConfigDecorator implements TypeConfigDecoratorInterface
{
    protected function getKind(array $config) {
        /**
         * @var $astNode Node
         */
        $astNode = $config["astNode"];

        return $astNode->kind;
    }
}
