<?php

namespace Everywhere\Api\Contract\Schema;

interface ContextInterface
{
    /**
     * @return ViewerInterface
     */
    public function getViewer();
}