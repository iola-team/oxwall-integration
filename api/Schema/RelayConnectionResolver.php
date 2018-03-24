<?php

namespace Everywhere\Api\Schema;

use Everywhere\Api\Contract\Integration\UsersRepositoryInterface;
use Everywhere\Api\Contract\Schema\ConnectionObjectInterface;
use Everywhere\Api\Contract\Schema\ContextInterface;
use Everywhere\Api\Contract\Schema\DataLoaderFactoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderInterface;
use Everywhere\Api\Entities\User;
use Everywhere\Api\Schema\AbstractResolver;
use Everywhere\Api\Schema\CompositeResolver;
use GraphQL\Executor\Promise\PromiseAdapter;
use GraphQL\Type\Definition\ResolveInfo;

class RelayConnectionResolver extends CompositeResolver
{
    /**
     * @var DataLoaderInterface
     */
    protected $itemsLoader;

    /**
     * @var DataLoaderInterface
     */
    protected $countLoader;

    public function __construct(DataLoaderFactoryInterface $dataLoaderFactory)
    {
        parent::__construct();

        $this->itemsLoader = $dataLoaderFactory->create(function($connections, $args) {
            $connection = reset($connections);

            return [
                (string) $connection => $this->getItems($connection, $args)
            ];
        });

        $this->countLoader = $dataLoaderFactory->create(function($connections, $args) {
            $connection = reset($connections);

            return [
                (string) $connection => $this->getCount($connection, $args)
            ];
        });
    }

    private function sanitizeArguments($arguments)
    {
        $sanitized = array_diff_key((array) $arguments, array_flip([
            "first",
            "after",
            "last",
            "before"
        ]));

        return $sanitized;
    }

    protected function buildArguments($arguments) {
        $newArguments = empty($arguments["after"]) ? $arguments : $arguments["after"];

        return array_merge(
            [
                "offset" => 0,
                "count" => $arguments["first"]
            ],
            $this->sanitizeArguments($newArguments)
        );
    }

    protected function buildCursor($item, $arguments, $index)
    {
        return array_merge(
            $this->sanitizeArguments($arguments),
            [
                "offset" => $index + 1,
                "count" => $arguments["first"]
            ]
        );
    }

    protected function buildEdge($item, $cursor)
    {
        return [
            "cursor" => $cursor,
            "node" => $item
        ];
    }

    protected function buildPageInfo($edges)
    {
        $last = end($edges);
        $first = reset($edges);

        return [
            "hasNextPage" => true,
            "hasPreviousPage" => false,
            "startCursor" => empty($first) ? null : $first["cursor"],
            "endCursor" => empty($last) ? null : $last["cursor"],
        ];
    }


    /**
     * @param ConnectionObjectInterface $connection
     * @param $arguments
     * @return mixed
     */
    protected function getCount(ConnectionObjectInterface $connection, $arguments)
    {
        return $connection->getCount($arguments);
    }

    protected function getItems(ConnectionObjectInterface $connection, $arguments)
    {
        return $connection->getItems($arguments);
    }

    private function getEdges(ConnectionObjectInterface $connection)
    {
        $arguments = $connection->getArguments();
        $itemsPromise = $this->itemsLoader->load($connection, $this->buildArguments($arguments));

        return $itemsPromise->then(function($items) use($arguments) {
            $edges = [];
            foreach ($items as $index => $item) {
                $cursor = $this->buildCursor($item, $arguments, $index);
                $edges[] = $this->buildEdge($item, $cursor);
            }

            return $edges;
        });
    }

    /**
     * @param ConnectionObjectInterface $connection
     * @param $args
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @return mixed
     */
    public function resolve($connection, $args, ContextInterface $context, ResolveInfo $info)
    {
        $value = parent::resolve($connection, $args, $context, $info);

        if ($value !== $this->undefined()) {
            return $value;
        }

        $edgesPromise = $this->getEdges($connection);

        $edgesPromise->then(function($edges) {
            return $edges;
        });

        switch ($info->fieldName) {
            case "pageInfo":
                return $edgesPromise->then(function($edges) {
                    return $this->buildPageInfo($edges);
                });

            case "edges":
                return $edgesPromise;

            case "totalCount":
                return $this->countLoader->load(
                    $connection, $this->sanitizeArguments($connection->getArguments())
                );
        }

        return $value;
    }
}
