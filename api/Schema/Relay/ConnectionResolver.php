<?php

namespace Everywhere\Api\Schema\Relay;

use Everywhere\Api\Contract\Integration\UserRepositoryInterface;
use Everywhere\Api\Contract\Schema\ConnectionObjectInterface;
use Everywhere\Api\Contract\Schema\ContextInterface;
use Everywhere\Api\Contract\Schema\DataLoaderFactoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderInterface;
use Everywhere\Api\Contract\Schema\Relay\EdgeFactoryInterface;
use Everywhere\Api\Entities\User;
use Everywhere\Api\Schema\AbstractResolver;
use Everywhere\Api\Schema\CompositeResolver;
use GraphQL\Executor\Promise\PromiseAdapter;
use GraphQL\Type\Definition\ResolveInfo;

class ConnectionResolver extends CompositeResolver
{
    /**
     * @var EdgeFactoryInterface
     */
    protected $edgeFactory;

    private $paginationArguments = [
        "first" => 0,
        "after" => null,
        "last" => 0,
        "before" => null
    ];

    public function __construct(EdgeFactoryInterface $edgeFactory)
    {
        parent::__construct();

        $this->edgeFactory = $edgeFactory;
    }

    private function getFilter(ConnectionObjectInterface $connection)
    {
        return array_diff_key(
            $connection->getArguments(),
            $this->paginationArguments
        );
    }

    private function getPagination(ConnectionObjectInterface $connection)
    {
        return array_intersect_key(
            array_merge($this->paginationArguments, $connection->getArguments()),
            $this->paginationArguments
        );
    }

    private function getEdges($items, ConnectionObjectInterface $connection)
    {
        $cursor = $this->getPagination($connection)["after"];

        $edges = [];
        foreach ($items as $index => $item) {
            $edge = $cursor
                ? $this->edgeFactory->createAfter($item, $cursor)
                : $this->edgeFactory->create($item, $this->getFilter($connection));

            $cursor = $edge->getCursor();
            $edges[] = $edge;
        }

        return $edges;
    }

    private function getItemsWithOverflow(ConnectionObjectInterface $connection)
    {
        $pagination = $this->getPagination($connection);
        $filter = $this->getFilter($connection);
        $offset = $pagination["after"] ? $pagination["after"]["offset"] + 1 : 0;
        $count = $pagination['first'] + 1;

        return $this->getItems(
            $connection,
            $filter,
            $offset,
            $count
        );
    }

    private function getPageInfo($items, ConnectionObjectInterface $connection)
    {
        $count = $this->getPagination($connection)["first"];
        $edges = $this->getEdges(
            array_slice($items, 0, $count),
            $connection
        );

        /**
         * @var $last EdgeObject
         */
        $last = end($edges);

        /**
         * @var $first EdgeObject
         */
        $first = reset($edges);

        return [
            "hasNextPage" => count($items) > count($edges),
            "hasPreviousPage" => empty($first) ? false : $first->getCursor()["offset"] > 0,
            "startCursor" => empty($first) ? null : $first->getCursor(),
            "endCursor" => empty($last) ? null : $last->getCursor()
        ];
    }

    /**
     * @param ConnectionObjectInterface $connection
     * @param array $filter
     * @param $offset
     * @param $count
     *
     * @return \GraphQL\Executor\Promise\Promise
     */
    protected function getItems(ConnectionObjectInterface $connection, array $filter, $offset, $count)
    {
        return $connection->getItems(array_merge([
            "offset" => $offset,
            "count" => $count
        ], $filter));
    }

    /**
     * @param ConnectionObjectInterface $connection
     * @param array $filter
     *
     * @return \GraphQL\Executor\Promise\Promise
     */
    protected function getCount(ConnectionObjectInterface $connection, array $filter)
    {
        return $connection->getCount($filter);
    }

    /**
     * @param ConnectionObjectInterface $connection
     * @param $args
     * @param ContextInterface $context
     * @param ResolveInfo $info
     *
     * @return mixed
     */
    public function resolve($connection, $args, ContextInterface $context, ResolveInfo $info)
    {
        $value = parent::resolve($connection, $args, $context, $info);

        if ($value !== $this->undefined()) {
            return $value;
        }

        $connectionArgs = $connection->getArguments();

        switch ($info->fieldName) {
            case "pageInfo":
                return $this->getItemsWithOverflow($connection)->then(function($items) use($connection) {
                    return $this->getPageInfo($items, $connection);
                });

            case "edges":
                return $this->getItemsWithOverflow($connection)->then(function($items) use($connection) {
                    return $this->getEdges($items, $connection);
                });

            case "totalCount":
                return $this->getCount($connection, $this->getFilter($connection));
        }

        return $value;
    }
}

