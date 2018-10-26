<?php
/**
 * Created by PhpStorm.
 * User: skambalin
 * Date: 19.10.17
 * Time: 14.37
 */

namespace Everywhere\Api\Contract\Schema;

use GraphQL\Type\Schema;

interface BuilderInterface
{
    /**
     * @return Schema
     */
    public function build();
}