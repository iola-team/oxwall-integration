<?php

namespace Iola\Api\Contract;

interface ServerInterface
{
    public function init();

    /**
     * @param string $path
     */
    public function run($path);
}
