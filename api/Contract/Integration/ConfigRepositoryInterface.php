<?php

namespace Everywhere\Api\Contract\Integration;

interface ConfigRepositoryInterface
{
    /**
     * @param array $args
     *
     * @return array
     */
    public function getAll($args);
}
