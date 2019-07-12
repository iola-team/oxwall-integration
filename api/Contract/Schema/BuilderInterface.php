<?php

namespace Iola\Api\Contract\Schema;

use GraphQL\Type\Schema;

interface BuilderInterface
{
    /**
     * @return Schema
     */
    public function build();
}