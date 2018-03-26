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
    private function sanitizeArguments($arguments, $blackList = [])
    {
        $sanitized = array_diff_key((array) $arguments, array_flip(array_merge([
            "first",
            "after",
            "last",
            "before"
        ], $blackList)));

        return $sanitized;
    }

    protected function buildArguments($arguments) {
        $newArguments = empty($arguments["after"])
            ? $this->sanitizeArguments($arguments)
            : (array) $arguments["after"];

        return array_merge(
            [
                "offset" => 0,
                "count" => $arguments["first"]
            ],
            $newArguments
        );
    }

    protected function buildCursor($item, $arguments, $index)
    {
        $cursor = isset($arguments["after"]) ? $arguments["after"] : [
            "offset" => 0
        ];

        return array_merge(
            $this->sanitizeArguments($arguments),
            [
                "offset" => $cursor["offset"] + $index + 1
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
        $itemsPromise = $this->getItems($connection, $this->buildArguments($arguments));

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

        switch ($info->fieldName) {
            case "pageInfo":
                return $this->getEdges($connection)->then(function($edges) {
                    return $this->buildPageInfo($edges);
                });

            case "edges":
                return $this->getEdges($connection);

            case "totalCount":
                return $this->getCount($connection, $this->sanitizeArguments($connection->getArguments()));
        }

        return $value;
    }
}
