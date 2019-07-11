<?php

namespace Iola\Api\Schema;

use Iola\Api\Contract\Schema\ContextInterface;
use Iola\Api\Contract\Schema\ViewerInterface;

class Context implements ContextInterface
{
    /**
     * @var ViewerInterface
     */
    protected $viewer;

    public function __construct(ViewerInterface $viewer)
    {
        $this->viewer = $viewer;
    }

    public function getViewer()
    {
        return $this->viewer;
    }
}