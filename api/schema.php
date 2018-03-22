<?php
namespace Everywhere\Api;

use Everywhere\Api\Schema\Resolvers\AuthenticationResolver;
use Everywhere\Api\Schema\Resolvers\AvatarResolver;
use Everywhere\Api\Schema\Resolvers\DateResolver;
use Everywhere\Api\Schema\Resolvers\NodeResolver;
use Everywhere\Api\Schema\Resolvers\QueryResolver;
use Everywhere\Api\Schema\Resolvers\UserConnectionResolver;
use Everywhere\Api\Schema\Resolvers\UserEdgeResolver;
use Everywhere\Api\Schema\Resolvers\UserResolver;
use Everywhere\Api\Schema\Resolvers\PhotoResolver;
use Everywhere\Api\Schema\Resolvers\CommentResolver;


return [
    "path" => __DIR__ . "/Schema.graphqls",
    "resolvers" => [
        // Object types

        "Query" => QueryResolver::class,

        "User" => UserResolver::class,
        "UserConnection" => UserConnectionResolver::class,
        "UserEdge" => UserEdgeResolver::class,

        "Photo" => PhotoResolver::class,
        "Comment" => CommentResolver::class,
        "Avatar" => AvatarResolver::class,

        // Scalar types
        "Date" => DateResolver::class,

        // Interface types
        'Node' => NodeResolver::class,

        // Mutation resolvers

        "Mutation" => [
            AuthenticationResolver::class,
        ]
    ],
];
