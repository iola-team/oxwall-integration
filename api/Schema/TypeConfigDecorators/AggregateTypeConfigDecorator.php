<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Schema\TypeConfigDecorators;

use Iola\Api\Contract\Schema\TypeConfigDecoratorInterface;
use Iola\Api\Schema\AbstractTypeConfigDecorator as TypeConfigDecorator;

class AggregateTypeConfigDecorator extends TypeConfigDecorator
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
