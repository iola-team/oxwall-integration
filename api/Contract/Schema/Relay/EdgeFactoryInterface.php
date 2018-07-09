<?php

namespace Everywhere\Api\Contract\Schema\Relay;

use Everywhere\Api\Contract\Entities\EntityInterface;

interface EdgeFactoryInterface
{
    /**
     * Creates an edge object based on provided entity object and connection arguments
     * Creates default cursor if $node was not provided
     *
     * @param array $filter
     * @param EntityInterface|string $node
     *
     * @return EdgeObjectInterface
     */
    public function create($filter, $node = null);

    /**
     * Creates an edge object for an entity after a cursor
     *
     * @param array $cursor
     * @param EntityInterface|string $node
     *
     * @return EdgeObjectInterface
     */
    public function createBefore($cursor, $node);

    /**
     * Creates an edge object for an entity before a cursor
     *
     * @param array $cursor
     * @param EntityInterface|string $node
     * @return EdgeObjectInterface
     */
    public function createAfter($cursor, $node);

    /**
     * @param $arguments
     * @param $node
     *
     * @return EdgeObjectInterface
     */
    public function createFromArguments($arguments, $node);
}
