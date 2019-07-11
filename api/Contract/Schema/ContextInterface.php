<?php

namespace Iola\Api\Contract\Schema;

interface ContextInterface
{
    /**
     * @return ViewerInterface
     */
    public function getViewer();
}