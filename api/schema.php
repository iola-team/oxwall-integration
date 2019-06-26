<?php
namespace Everywhere\Api;

use Everywhere\Api\Schema\Relay;
use Everywhere\Api\Schema\Resolvers\AccountTypeResolver;
use Everywhere\Api\Schema\Resolvers\AuthMutationResolver;
use Everywhere\Api\Schema\Resolvers\AvatarMutationResolver;
use Everywhere\Api\Schema\Resolvers\AvatarResolver;
use Everywhere\Api\Schema\Resolvers\ChatResolver;
use Everywhere\Api\Schema\Resolvers\CursorResolver;
use Everywhere\Api\Schema\Resolvers\DateResolver;
use Everywhere\Api\Schema\Resolvers\MessageSubscriptionResolver;
use Everywhere\Api\Schema\Resolvers\MessageMutationResolver;
use Everywhere\Api\Schema\Resolvers\MessageResolver;
use Everywhere\Api\Schema\Resolvers\NodeResolver;
use Everywhere\Api\Schema\Resolvers\PhotoMutationResolver;
use Everywhere\Api\Schema\Resolvers\PhotoCommentSubscriptionResolver;
use Everywhere\Api\Schema\Resolvers\PresentationAwareTypeResolver;
use Everywhere\Api\Schema\Resolvers\ProfileFieldResolver;
use Everywhere\Api\Schema\Resolvers\ProfileFieldSectionResolver;
use Everywhere\Api\Schema\Resolvers\ProfileFieldValueResolver;
use Everywhere\Api\Schema\Resolvers\ProfileMutationResolver;
use Everywhere\Api\Schema\Resolvers\QueryResolver;
use Everywhere\Api\Schema\Resolvers\UploadResolver;
use Everywhere\Api\Schema\Resolvers\ProfileResolver;
use Everywhere\Api\Schema\Resolvers\UserResolver;
use Everywhere\Api\Schema\Resolvers\UserSubscriptionResolver;
use Everywhere\Api\Schema\Resolvers\UserInfoResolver;
use Everywhere\Api\Schema\Resolvers\PhotoResolver;
use Everywhere\Api\Schema\Resolvers\CommentResolver;
use Everywhere\Api\Schema\Resolvers\ValueResolver;
use Everywhere\Api\Schema\Resolvers\FriendMutationResolver;
use Everywhere\Api\Schema\Resolvers\FriendshipResolver;
use Everywhere\Api\Schema\Resolvers\UserFriendsConnectionResolver;
use Everywhere\Api\Schema\Resolvers\UserChatsConnectionResolver;
use Everywhere\Api\Schema\Resolvers\ChatMessagesConnectionResolver;
use Everywhere\Api\Schema\Resolvers\ChatEdgeResolver;
use Everywhere\Api\Schema\Resolvers\FriendshipSubscriptionResolver;
use Everywhere\Api\Schema\Resolvers\ReportMutationResolver;

return [
    "path" => __DIR__ . "/Schema.graphql",
    "resolvers" => [
        // Object types

        "Query" => QueryResolver::class,

        "User" => UserResolver::class,
        "UserInfo" => UserInfoResolver::class,
        "UserConnection" => Relay\ConnectionResolver::class,
        "UserFriendsConnection" => UserFriendsConnectionResolver::class,
        "UserPhotoConnection" => Relay\ConnectionResolver::class,
        "UserChatsConnection" => UserChatsConnectionResolver::class,
        "ChatMessagesConnection" => ChatMessagesConnectionResolver::class,

        "Photo" => PhotoResolver::class,
        "Comment" => CommentResolver::class,
        "PhotoCommentsConnection" => Relay\ConnectionResolver::class,
        "Avatar" => AvatarResolver::class,
        "AccountType" => AccountTypeResolver::class,
        "ProfileField" => ProfileFieldResolver::class,
        "ProfileFieldValue" => ProfileFieldValueResolver::class,
        "ProfileFieldSection" => ProfileFieldSectionResolver::class,
        "Profile" => ProfileResolver::class,
        "Chat" => ChatResolver::class,
        "ChatEdge" => ChatEdgeResolver::class,
        "Message" => MessageResolver::class,
        "Friendship" => FriendshipResolver::class,

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
            ProfileMutationResolver::class,
            MessageMutationResolver::class,
            FriendMutationResolver::class,
            ReportMutationResolver::class,
        ],

        "Subscription" => [
            UserSubscriptionResolver::class,
            MessageSubscriptionResolver::class,
            PhotoCommentSubscriptionResolver::class,
            FriendshipSubscriptionResolver::class
        ]
    ]
];
