<?php
/**
 * Created by PhpStorm.
 * User: skambalin
 * Date: 19.10.17
 * Time: 15.35
 */

namespace Everywhere\Api\Contract\Schema;


interface TypeConfigDecoratorInterface
{
    public function decorate(array $typeConfig);
}