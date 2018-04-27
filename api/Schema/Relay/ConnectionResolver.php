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
        $edges = [];
        $firstItem = array_shift($items);
        $after = $this->getPagination($connection)["after"];
        $filter = $after ? $after : $this->getFilter($connection);
        
        if ($firstItem) {
            $firstEdge = $this->edgeFactory->createAfter($filter, $firstItem);
            $edges[] = $firstEdge;
        } else {
            $firstEdge = $this->edgeFactory->create($filter);
        }

        $resultPromise = $firstEdge->getCursor();

        foreach ($items as $index => $item) {
            $resultPromise = $resultPromise->then(function($cursor) use($item, &$edges) {
                $edge = $this->edgeFactory->createAfter($cursor, $item);
                $edges[] = $edge;

                return $edge->getCursor();
            });
        }

        return $resultPromise->then(function() use(&$edges) {
            return $edges;
        });
    }

    private function getItemsWithOverflow(ConnectionObjectInterface $connection)
    {
        $pagination = $this->getPagination($connection);
        $filter = $this->getFilter($connection);
        $offset = 0;
        $count = $pagination['first'] + 1;

        if ($pagination["after"]) {
            $offset = $pagination["after"]["offset"] + 1;
            $filter = $pagination["after"];
        }

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
        $edgesPromise = $this->getEdges(
            array_slice($items, 0, $count),
            $connection
        );

        return $edgesPromise->then(function($edges) use($items) {
            /**
             * @var $last EdgeObject
             */
            $last = end($edges);

            /**
             * @var $first EdgeObject
             */
            $first = reset($edges);

            $hasPreviousPage = empty($first) ? false : $first->getCursor()->then(function($cursor) {
                return $cursor["offset"] > 0;
            });

            return [
                "hasNextPage" => count($items) > count($edges),
                "hasPreviousPage" => $hasPreviousPage,
                "startCursor" => empty($first) ? null : $first->getCursor(),
                "endCursor" => empty($last) ? null : $last->getCursor()
            ];
        });
    }

    private function getMeta(ConnectionObjectInterface $connection)
    {
        $firstEdgePlaceholder = $this->edgeFactory->create($this->getFilter($connection));

        return [
            "firstCursor" => $firstEdgePlaceholder->getCursor()
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
        return $connection->getItems(array_merge($filter, [
            "offset" => $offset,
            "count" => $count
        ]));
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

            case "metaInfo":
                return $this->getMeta($connection);

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

