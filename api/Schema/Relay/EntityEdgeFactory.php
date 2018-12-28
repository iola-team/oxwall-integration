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

    protected function loadNode($item)
    {
        if (!$item || $item instanceof EntityInterface) {
            return $this->promiseAdapter->createFulfilled($item);
        }

        return $this->entityLoader->load($item);
    }

    protected function buildCursor($item, $fromCursor, $direction)
    {
        return $this->loadNode($item)->then(function($node) use($fromCursor, $direction) {
            return $this->buildEntityCursor($node, $fromCursor, $direction);
        });
    }

    protected function buildEntityCursor(EntityInterface $entity, $fromCursor, $direction)
    {
        return [];
    }
}
