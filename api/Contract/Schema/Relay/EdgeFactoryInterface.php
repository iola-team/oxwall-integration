<?php

namespace Everywhere\Api\Contract\Schema\Relay;

use Everywhere\Api\Contract\Entities\EntityInterface;

interface EdgeFactoryInterface
{
    /**
     * Create an edge object based on provided entity object and connection arguments
     *
     * @param EntityInterface|string $node
     * @param $filterArguments
     * @return EdgeObjectInterface
     */
    public function create($node, $filterArguments);

    /**
     * Creates an edge object for an entity after a cursor
     *
     * @param EntityInterface|string $node
     * @param $cursor
     * @return EdgeObjectInterface
     */
    public function createBefore($node, $cursor);

    /**
     * Creates an edge object for an entity before a cursor
     *
     * @param EntityInterface|string $node
     * @param $cursor
     * @return EdgeObjectInterface
     */
    public function createAfter($node, $cursor);
}
