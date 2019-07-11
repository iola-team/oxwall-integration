<?php

namespace Iola\Api\Contract\Schema;


interface TypeConfigDecoratorInterface
{
    public function decorate(array $typeConfig);
}