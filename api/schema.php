<?php
namespace Iola\Api;

use Iola\Api\Schema\Relay;
use Iola\Api\Schema\Resolvers\AccountTypeResolver;
use Iola\Api\Schema\Resolvers\AuthMutationResolver;
use Iola\Api\Schema\Resolvers\AvatarMutationResolver;
use Iola\Api\Schema\Resolvers\AvatarResolver;
use Iola\Api\Schema\Resolvers\BlockMutationResolver;
use Iola\Api\Schema\Resolvers\ChatResolver;
use Iola\Api\Schema\Resolvers\CursorResolver;
use Iola\Api\Schema\Resolvers\DateResolver;
use Iola\Api\Schema\Resolvers\MessageSubscriptionResolver;
use Iola\Api\Schema\Resolvers\MessageMutationResolver;
use Iola\Api\Schema\Resolvers\MessageResolver;
use Iola\Api\Schema\Resolvers\NodeResolver;
use Iola\Api\Schema\Resolvers\PhotoMutationResolver;
use Iola\Api\Schema\Resolvers\PhotoCommentSubscriptionResolver;
use Iola\Api\Schema\Resolvers\PresentationAwareTypeResolver;
use Iola\Api\Schema\Resolvers\ProfileFieldResolver;
use Iola\Api\Schema\Resolvers\ProfileFieldSectionResolver;
use Iola\Api\Schema\Resolvers\ProfileFieldValueResolver;
use Iola\Api\Schema\Resolvers\ProfileMutationResolver;
use Iola\Api\Schema\Resolvers\QueryResolver;
use Iola\Api\Schema\Resolvers\UploadResolver;
use Iola\Api\Schema\Resolvers\ProfileResolver;
use Iola\Api\Schema\Resolvers\UserResolver;
use Iola\Api\Schema\Resolvers\UserSubscriptionResolver;
use Iola\Api\Schema\Resolvers\UserInfoResolver;
use Iola\Api\Schema\Resolvers\PhotoResolver;
use Iola\Api\Schema\Resolvers\CommentResolver;
use Iola\Api\Schema\Resolvers\ValueResolver;
use Iola\Api\Schema\Resolvers\FriendMutationResolver;
use Iola\Api\Schema\Resolvers\FriendshipResolver;
use Iola\Api\Schema\Resolvers\UserFriendsConnectionResolver;
use Iola\Api\Schema\Resolvers\UserChatsConnectionResolver;
use Iola\Api\Schema\Resolvers\ChatMessagesConnectionResolver;
use Iola\Api\Schema\Resolvers\ChatEdgeResolver;
use Iola\Api\Schema\Resolvers\FriendshipSubscriptionResolver;
use Iola\Api\Schema\Resolvers\ReportMutationResolver;
use Iola\Api\Schema\Resolvers\UserMutationResolver;

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
            UserMutationResolver::class,
            BlockMutationResolver::class
        ],

        "Subscription" => [
            UserSubscriptionResolver::class,
            MessageSubscriptionResolver::class,
            PhotoCommentSubscriptionResolver::class,
            FriendshipSubscriptionResolver::class
        ]
    ]
];
