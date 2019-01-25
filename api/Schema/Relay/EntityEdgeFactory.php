<?php

namespace Everywhere\Api\Schema\Relay;

use Everywhere\Api\Contract\Entities\EntityInterface;
use Everywhere\Api\Contract\Schema\DataLoaderInterface;
use GraphQL\Executor\Promise\PromiseAdapter;
use Everywhere\Api\Contract\Schema\IDObjectInterface;

class EntityEdgeFactory extends EdgeFactory
{
    /**
     * @var DataLoaderInterface
     */
    protected $entityLoader;

    public function __construct(DataLoaderInterface $entityLoader, PromiseAdapter $promiseAdapter)
    {
        parent::__construct($promiseAdapter);

        $this->entityLoader = $entityLoader;
    }

    /**
     * Loads entity for provided root value
     * TODO: This logic is copied from `EntityResolver` - move it to some common place
     *
     * @param [type] $rootValue
     * @return void
     */
    protected function getNode($rootValue)
    {
        $node = parent::getNode($rootValue);

        if ($node === null) {
            return null;
        }

        $id = $node instanceof IDObjectInterface
            ? $node->getId()
            : $node;

        if ($node instanceof EntityInterface) {
            $id = $node->getId();
            $this->entityLoader->prime($id, $node);
        }

        return $this->entityLoader->load($id);
    }

    protected function getCursor($rootValue, $fromCursor, $direction)
    {
        return $this->getNode($rootValue)->then(function($node) use($fromCursor, $direction) {
            return $this->buildEntityCursor($node, $fromCursor, $direction);
        });
    }

    /**
     * @param EntityInterface $entity
     * @param array $fromCursor
     * @param int $direction
     * @return array
     */
    protected function getEntityCursor($entity, $fromCursor, $direction)
    {
        return [];
    }
}
