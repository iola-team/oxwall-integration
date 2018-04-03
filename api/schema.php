<?php
namespace Everywhere\Api;

use Everywhere\Api\Schema\RelayConnectionResolver;
use Everywhere\Api\Schema\Resolvers\AuthenticationResolver;
use Everywhere\Api\Schema\Resolvers\AvatarResolver;
use Everywhere\Api\Schema\Resolvers\CursorResolver;
use Everywhere\Api\Schema\Resolvers\DateResolver;
use Everywhere\Api\Schema\Resolvers\NodeResolver;
use Everywhere\Api\Schema\Resolvers\QueryResolver;
use Everywhere\Api\Schema\Resolvers\UserInfoResolver;
use Everywhere\Api\Schema\Resolvers\UserResolver;
use Everywhere\Api\Schema\Resolvers\PhotoResolver;
use Everywhere\Api\Schema\Resolvers\CommentResolver;


return [
    "path" => __DIR__ . "/Schema.graphqls",
    "resolvers" => [
        // Object types

        "Query" => QueryResolver::class,

        "User" => UserResolver::class,
        "UserInfo" => UserInfoResolver::class,
        "UserConnection" => RelayConnectionResolver::class,
        "UserFriendsConnection" => RelayConnectionResolver::class,
        "UserPhotoConnection" => RelayConnectionResolver::class,

        "Photo" => PhotoResolver::class,
        "Comment" => CommentResolver::class,
        "Avatar" => AvatarResolver::class,

        // Scalar types
        "Date" => DateResolver::class,
        "Cursor" => CursorResolver::class,

        // Interface types
        'Node' => NodeResolver::class,

        // Mutation resolvers

        "Mutation" => [
            AuthenticationResolver::class,
        ]
    ]
];
