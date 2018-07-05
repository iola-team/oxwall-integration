<?php
namespace Everywhere\Api;

use Everywhere\Api\Schema\Relay;
use Everywhere\Api\Schema\Resolvers\AccountTypeResolver;
use Everywhere\Api\Schema\Resolvers\AuthMutationResolver;
use Everywhere\Api\Schema\Resolvers\AvatarMutationResolver;
use Everywhere\Api\Schema\Resolvers\AvatarResolver;
use Everywhere\Api\Schema\Resolvers\CursorResolver;
use Everywhere\Api\Schema\Resolvers\DateResolver;
use Everywhere\Api\Schema\Resolvers\NewMessageSubscriptionResolver;
use Everywhere\Api\Schema\Resolvers\NodeResolver;
use Everywhere\Api\Schema\Resolvers\PhotoMutationResolver;
use Everywhere\Api\Schema\Resolvers\PresentationAwareTypeResolver;
use Everywhere\Api\Schema\Resolvers\ProfileFieldConfigsResolver;
use Everywhere\Api\Schema\Resolvers\ProfileFieldResolver;
use Everywhere\Api\Schema\Resolvers\ProfileFieldSectionResolver;
use Everywhere\Api\Schema\Resolvers\ProfileFieldValueResolver;
use Everywhere\Api\Schema\Resolvers\ProfileMutationResolver;
use Everywhere\Api\Schema\Resolvers\QueryResolver;
use Everywhere\Api\Schema\Resolvers\UploadResolver;
use Everywhere\Api\Schema\Resolvers\ProfileResolver;
use Everywhere\Api\Schema\Resolvers\UserResolver;
use Everywhere\Api\Schema\Resolvers\UserInfoResolver;
use Everywhere\Api\Schema\Resolvers\PhotoResolver;
use Everywhere\Api\Schema\Resolvers\CommentResolver;
use Everywhere\Api\Schema\Resolvers\ValueResolver;

return [
    "path" => __DIR__ . "/Schema.graphqls",
    "resolvers" => [
        // Object types

        "Query" => QueryResolver::class,

        "User" => UserResolver::class,
        "UserInfo" => UserInfoResolver::class,
        "UserConnection" => Relay\ConnectionResolver::class,
        "UserFriendsConnection" => Relay\ConnectionResolver::class,
        "UserPhotoConnection" => Relay\ConnectionResolver::class,

        "Photo" => PhotoResolver::class,
        "Comment" => CommentResolver::class,
        "Avatar" => AvatarResolver::class,
        "AccountType" => AccountTypeResolver::class,
        "ProfileField" => ProfileFieldResolver::class,
        "ProfileFieldValue" => ProfileFieldValueResolver::class,
        "ProfileFieldSection" => ProfileFieldSectionResolver::class,
        "Profile" => ProfileResolver::class,

        // Scalar types
        "Value" => ValueResolver::class,
        "Date" => DateResolver::class,
        "Cursor" => CursorResolver::class,
        "Upload" => UploadResolver::class,

        // Interface types
        "Node" => NodeResolver::class,

        // Union types
        "ProfileFieldConfigs" => PresentationAwareTypeResolver::class,
        "ProfileFieldValueData" => PresentationAwareTypeResolver::class,

        // Mutation resolvers

        "Mutation" => [
            AuthMutationResolver::class,
            AvatarMutationResolver::class,
            PhotoMutationResolver::class,
            ProfileMutationResolver::class
        ],

        "Subscription" => [
            NewMessageSubscriptionResolver::class
        ]
    ]
];
