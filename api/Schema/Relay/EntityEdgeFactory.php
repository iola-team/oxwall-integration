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

    protected function getNode($rootValue)
    {
        $node = parent::getNode($rootValue);

        if (!$node || $node instanceof EntityInterface) {
            return $this->promiseAdapter->createFulfilled($node);
        }

        return $this->entityLoader->load($node);
    }

    protected function getCursor($rootValue, $fromCursor, $direction)
    {
        return $this->getNode($rootValue)->then(function($node) use($fromCursor, $direction) {
            return $this->buildEntityCursor($node, $fromCursor, $direction);
        });
    }

    protected function getEntityCursor(EntityInterface $entity, $fromCursor, $direction)
    {
        return [];
    }
}
