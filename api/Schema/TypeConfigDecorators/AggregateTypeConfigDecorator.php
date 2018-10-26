<?php

namespace Everywhere\Api\Schema\TypeConfigDecorators;

use Everywhere\Api\Contract\Schema\TypeConfigDecoratorInterface;
use Everywhere\Api\Schema\AbstractTypeConfigDecorator;

class AggregateTypeConfigDecorator extends AbstractTypeConfigDecorator
{
    protected $decorators = [];

    public function __construct(array $decorators = [])
    {
        foreach ($decorators as $decorator) {
            $this->addDecorator($decorator);
        }
    }

    public function addDecorator(TypeConfigDecoratorInterface $decorator)
    {
        $this->decorators[] = $decorator;
    }

    public function decorate(array $typeConfig)
    {
        /**
         * @var $decorator TypeConfigDecoratorInterface
         */
        foreach ($this->decorators as $decorator) {
            $typeConfig = $decorator->decorate($typeConfig);
        }

        return $typeConfig;
    }
}
