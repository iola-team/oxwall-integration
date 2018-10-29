<?php

namespace Everywhere\Api\Schema\Relay;

use Everywhere\Api\Contract\Entities\EntityInterface;
use Everywhere\Api\Contract\Schema\DataLoaderInterface;
use GraphQL\Executor\Promise\PromiseAdapter;

class EntityEdgeFactory extends EdgeFactory
{
    protected $entityLoader;

    public function __construct(DataLoaderInterface $entityLoader, PromiseAdapter $promiseAdapter)
    {
        parent::__construct($promiseAdapter);

        $this->entityLoader = $entityLoader;
    }

    protected function loadNode($node)
    {
        if (!$node || $node instanceof EntityInterface) {
            return $node;
        }

        return $this->entityLoader->load($node);
    }
}
