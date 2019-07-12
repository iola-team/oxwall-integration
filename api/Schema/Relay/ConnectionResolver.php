<?php

namespace Iola\Api\Schema\Relay;

use Iola\Api\Contract\Schema\ConnectionObjectInterface;
use Iola\Api\Contract\Schema\ContextInterface;
use Iola\Api\Contract\Schema\Relay\EdgeFactoryInterface;
use Iola\Api\Schema\CompositeResolver;
use GraphQL\Error\InvariantViolation;
use GraphQL\Type\Definition\ResolveInfo;

class ConnectionResolver extends CompositeResolver
{
    const DEFAULT_COUNT = 100;

    /**
     * @var EdgeFactoryInterface
     */
    protected $edgeFactory;

    private $paginationArguments = [
        "first" => null,
        "after" => null,
        "last" => null,
        "before" => null
    ];

    public function __construct(EdgeFactoryInterface $edgeFactory)
    {
        parent::__construct();

        $this->edgeFactory = $edgeFactory;
    }

    private function prepareArguments(ConnectionObjectInterface $connection)
    {
        return array_diff_key(
            $connection->getArguments(),
            $this->paginationArguments
        );
    }

    private function getPagination(ConnectionObjectInterface $connection)
    {
        $pagination = array_intersect_key(
            array_merge($this->paginationArguments, $connection->getArguments()),
            $this->paginationArguments
        );

        if ($pagination["last"] !== null && $pagination["first"] !== null) {
            throw new InvariantViolation("You should not use `last` and `first` pagination simultaneously");
        }

        if ($pagination["last"] === null && $pagination["first"] === null) {
            $pagination["first"] = self::DEFAULT_COUNT;
        }

        return $pagination;
    }

    /**
     * Cursor may include any extra data we pass here.
     * This data can be the connection arguments...
     *
     * TODO: Review the code later and add arguments data to cursors if needed
     *
     * @param ConnectionObjectInterface $connection
     *
     * @return array
     */
    private function getInitialCursorData(ConnectionObjectInterface $connection)
    {
        return [];
    }

    private function getEdges($items, ConnectionObjectInterface $connection, $slice)
    {
        $offset = $slice["offset"];
        $items = $this->sliceEdges($items, $slice["count"]);

        $cursorData = array_merge([
            "offset" => $offset
        ], $this->getInitialCursorData($connection));

        $firstItem = array_shift($items);
        $firstEdge = $this->edgeFactory->create($cursorData, $firstItem);
        $edges = $firstItem ? [$firstEdge] : [];
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

    private function getItemsWithOverflow(ConnectionObjectInterface $connection, $totalCount = null)
    {
        $pagination = $this->getPagination($connection);
        $arguments = $this->prepareArguments($connection);
        $cursorArg = [];

        if ($pagination["after"]) {
            $cursorArg["after"] = $pagination["after"];
        }

        if ($pagination["before"]) {
            $cursorArg["before"] = $pagination["before"];
        }

        $slice = $this->getSlice($connection, $totalCount);

        return $this->getItems(
            $connection,
            array_merge($arguments, $cursorArg, [
                "offset" => $slice["offset"],
                "count" => $slice["count"] + 1 // Plus overflow to be able detect `hasNextPage` later
            ])
        );
    }


    private function getCursorOffset($cursor, $defaultValue = null)
    {
        return isset($cursor["offset"]) ? (int) $cursor["offset"] : $defaultValue;
    }

    private function isTotalCountRequired(ConnectionObjectInterface $connection)
    {
        $pagination = $this->getPagination($connection);

        return !$pagination["before"] && $pagination["first"] === null;
    }

    /**
     * Calculates slice based on pagination arguments
     *
     * @param ConnectionObjectInterface $connection
     * @param int|null $totalCount
     * @return array
     */
    private function getSlice(ConnectionObjectInterface $connection, $totalCount = null)
    {
        $pagination = $this->getPagination($connection);

        $endOffset = $this->getCursorOffset($pagination["before"], $totalCount);
        $afterOffset = $this->getCursorOffset($pagination["after"], -1);
        $startOffset = max($afterOffset,-1) + 1;

        if ($pagination["first"] !== null) {
            $endOffset = $startOffset + $pagination["first"];
        }

        if ($endOffset === null) {
            throw new InvariantViolation(
                "Connection `totalCount` loader or `before` cursor is required for pagination"
            );
        }

        if ($pagination["last"] !== null) {
            $startOffset = max($startOffset, $endOffset - $pagination["last"]);
        }

        $offset = max($startOffset, 0);
        $slice = [
            "offset" => $offset,
            "count" => $endOffset - $offset
        ];

        return $slice;
    }

    private function sliceEdges($items, $count)
    {
        return $items === null ? [] : array_slice($items, 0, $count);
    }

    private function getPageInfo($items, ConnectionObjectInterface $connection, $slice)
    {
        $pagination = $this->getPagination($connection);
        $edgesPromise = $this->getEdges(
            $this->sliceEdges($items, $slice["count"]),
            $connection,
            $slice
        );

        return $edgesPromise->then(function($edges) use($items, $pagination) {
            /**
             * @var $last EdgeObject
             */
            $last = end($edges);

            /**
             * @var $first EdgeObject
             */
            $first = reset($edges);

            $hasNextPage = empty($items) ? false : count($items) > count($edges);

            $hasPreviousPage = empty($first)
                ? $pagination["after"]["offset"] > 0
                : $first->getCursor()->then(function($cursor) {
                    return $cursor["offset"] > 0;
                }
            );

            return [
                "hasNextPage" => $hasNextPage,
                "hasPreviousPage" => $hasPreviousPage,
                "startCursor" => empty($first) ? null : $first->getCursor(),
                "endCursor" => empty($last) ? null : $last->getCursor()
            ];
        });
    }

    private function getMeta(ConnectionObjectInterface $connection)
    {
        $firstEdgePlaceholder = $this->edgeFactory->create($this->getInitialCursorData($connection));

        return [
            "firstCursor" => $firstEdgePlaceholder->getCursor()
        ];
    }

    /**
     * @param ConnectionObjectInterface $connection
     * @param array $arguments
     *
     * @return \GraphQL\Executor\Promise\Promise
     */
    protected function getItems(ConnectionObjectInterface $connection, array $arguments)
    {
        return $connection->getItems($arguments);
    }

    /**
     * @param ConnectionObjectInterface $connection
     *
     * @return \GraphQL\Executor\Promise\Promise
     */
    protected function getCount(ConnectionObjectInterface $connection, array $arguments)
    {
        return $connection->getCount($arguments);
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
        $totalPromise = $this->isTotalCountRequired($connection) 
            ? $this->getCount($connection, $this->prepareArguments($connection))
            : null;

        switch ($info->fieldName) {
            case "pageInfo":
                $getPageInfo = function($totalCount = null) use ($connection) {
                    return $this->getItemsWithOverflow($connection, $totalCount)->then(function($items) use($connection, $totalCount) {
                        return $this->getPageInfo($items, $connection, $this->getSlice($connection, $totalCount));
                    });
                };

                return $totalPromise ? $totalPromise->then($getPageInfo) : $getPageInfo();

            case "metaInfo":
                return $this->getMeta($connection);

            case "edges":
                $getEdges = function($totalCount = null) use ($connection) {
                    return $this->getItemsWithOverflow($connection, $totalCount)->then(function ($items) use ($connection, $totalCount) {
                        return $this->getEdges($items, $connection, $this->getSlice($connection, $totalCount));
                    });
                };

                return $totalPromise ? $totalPromise->then($getEdges) : $getEdges();

            case "totalCount":
                return $totalPromise ? $totalPromise : $this->getCount($connection, $this->prepareArguments($connection));
        }

        return $value;
    }
}

