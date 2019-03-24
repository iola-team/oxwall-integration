<?php
namespace Everywhere\Api;

use Everywhere\Api\App\EventManager;
use Everywhere\Api\Auth\AuthenticationAdapter;
use Everywhere\Api\Auth\AuthenticationService;
use Everywhere\Api\Auth\IdentityService;
use Everywhere\Api\Auth\IdentityStorage;
use Everywhere\Api\Auth\TokenBuilder;
use Everywhere\Api\Contract\App\ContainerInterface;
use Everywhere\Api\Contract\App\EventManagerInterface;
use Everywhere\Api\Contract\Auth\AuthenticationAdapterInterface;
use Everywhere\Api\Contract\Auth\AuthenticationServiceInterface;
use Everywhere\Api\Contract\Auth\IdentityServiceInterface;
use Everywhere\Api\Contract\Auth\IdentityStorageInterface;
use Everywhere\Api\Contract\Auth\TokenBuilderInterface;
use Everywhere\Api\Contract\Integration\EventSourceInterface;
use Everywhere\Api\Contract\Integration\SubscriptionRepositoryInterface;
use Everywhere\Api\Contract\Schema\ConnectionFactoryInterface;
use Everywhere\Api\Contract\Schema\ContextInterface;
use Everywhere\Api\Contract\Schema\DataLoaderFactoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderInterface;
use Everywhere\Api\Contract\Schema\IDFactoryInterface;
use Everywhere\Api\Contract\Schema\SubscriptionFactoryInterface;
use Everywhere\Api\Contract\Schema\ViewerInterface;
use Everywhere\Api\Contract\Subscription\SubscriptionManagerFactoryInterface;
use Everywhere\Api\Contract\Subscription\SubscriptionManagerInterface;
use Everywhere\Api\Controllers\GraphqlController;
use Everywhere\Api\Controllers\SubscriptionController;
use Everywhere\Api\Integration\EventSource;
use Everywhere\Api\Middleware\AuthenticationMiddleware;
use Everywhere\Api\Middleware\SubscriptionMiddleware;
use Everywhere\Api\Middleware\UploadMiddleware;
use Everywhere\Api\Middleware\CorsMiddleware;
use Everywhere\Api\Schema\ConnectionFactory;
use Everywhere\Api\Schema\Relay;
use Everywhere\Api\Schema\Resolvers\AccountTypeResolver;
use Everywhere\Api\Schema\Resolvers\AvatarMutationResolver;
use Everywhere\Api\Schema\Resolvers\ChatResolver;
use Everywhere\Api\Schema\Resolvers\CursorResolver;
use Everywhere\Api\Schema\Resolvers\DateResolver;
use Everywhere\Api\Schema\Resolvers\MessageMutationResolver;
use Everywhere\Api\Schema\Resolvers\MessageResolver;
use Everywhere\Api\Schema\Resolvers\MessageSubscriptionResolver;
use Everywhere\Api\Schema\Resolvers\NodeResolver;
use Everywhere\Api\Schema\Resolvers\PhotoMutationResolver;
use Everywhere\Api\Schema\Resolvers\PhotoCommentSubscriptionResolver;
use Everywhere\Api\Schema\Resolvers\PresentationAwareTypeResolver;
use Everywhere\Api\Schema\Resolvers\ProfileFieldResolver;
use Everywhere\Api\Schema\Resolvers\ProfileFieldSectionResolver;
use Everywhere\Api\Schema\Resolvers\ProfileFieldValueResolver;
use Everywhere\Api\Schema\Resolvers\ProfileMutationResolver;
use Everywhere\Api\Schema\Resolvers\UploadResolver;
use Everywhere\Api\Schema\Resolvers\UserInfoResolver;
use Everywhere\Api\Schema\Resolvers\ProfileResolver;
use Everywhere\Api\Schema\Resolvers\ValueResolver;
use Everywhere\Api\Schema\SubscriptionFactory;
use Everywhere\Api\Schema\TypeConfigDecorators\AggregateTypeConfigDecorator;
use Everywhere\Api\Schema\TypeConfigDecorators\AbstractTypeConfigDecorator;
use Everywhere\Api\Schema\TypeConfigDecorators\ObjectTypeConfigDecorator;
use Everywhere\Api\Schema\TypeConfigDecorators\ScalarTypeConfigDecorator;
use Everywhere\Api\Schema\Context;
use Everywhere\Api\Schema\DataLoaderFactory;
use Everywhere\Api\Schema\Builder;
use Everywhere\Api\Schema\IDFactory;
use Everywhere\Api\Schema\Resolvers\AuthMutationResolver;
use Everywhere\Api\Schema\Resolvers\AvatarResolver;
use Everywhere\Api\Contract\Schema\BuilderInterface;
use Everywhere\Api\Contract\Schema\TypeConfigDecoratorInterface;
use Everywhere\Api\Schema\Viewer;
use Everywhere\Api\Subscription\SubscriptionManager;
use Everywhere\Api\Subscription\SubscriptionManagerFactory;
use GraphQL\Server\ServerConfig;
use GraphQL\Executor\Promise\PromiseAdapter;
use GraphQL\Type\Schema;
use Overblog\DataLoader\Promise\Adapter\Webonyx\GraphQL\SyncPromiseAdapter;
use Everywhere\Api\Schema\Resolvers\QueryResolver;
use Everywhere\Api\Schema\Resolvers\UserResolver;
use Everywhere\Api\Schema\Resolvers\UserSubscriptionResolver;
use Everywhere\Api\Schema\Resolvers\PhotoResolver;
use Everywhere\Api\Schema\Resolvers\CommentResolver;
use Everywhere\Api\Schema\Resolvers\FriendMutationResolver;
use Everywhere\Api\Schema\Resolvers\FriendEdgeResolver;
use Everywhere\Api\Schema\Resolvers\FriendshipResolver;
use Everywhere\Api\Schema\DefaultResolver;
use Everywhere\Api\Schema\Resolvers\UserFriendsConnectionResolver;
use Everywhere\Api\Schema\Resolvers\UserFriendEdgeResolver;
use Everywhere\Api\Schema\Resolvers\UserChatsConnectionResolver;
use Everywhere\Api\Schema\Resolvers\ChatMessagesConnectionResolver;
use Everywhere\Api\Schema\Resolvers\ChatEdgeResolver;
use GraphQL\Validator\DocumentValidator;
use GraphQL\Validator\Rules\NoUnusedVariables;

return [
    PromiseAdapter::class => function() {
        return new SyncPromiseAdapter();
    },

    EventManagerInterface::class =>  function() {
        return new EventManager();
    },

    Schema::class => function(ContainerInterface $container) {
        return $container[BuilderInterface::class]->build();
    },

    ServerConfig::class => function(ContainerInterface $container) {
        $allValidationRules = DocumentValidator::allRules();

        /**
         * Disable unused variables validation.
         * When a variable is used in a query only for `@client` properties,
         * the server complains about unused variable,
         * since `apollo-client` strips all `@client` properties from the result query.
         *
         * TODO:
         * The issue first appered in `SearchResultHistoryQuery`.
         * Check if it still the case after updating apollo-client to 2.5.0+
         *
         */
        unset($allValidationRules[NoUnusedVariables::class]);

        return ServerConfig::create([
            "debug" => true,
            "queryBatching" => true,
            "context" => $container[ContextInterface::class],
            "schema" => $container[Schema::class],
            "promiseAdapter" => $container[PromiseAdapter::class],
            "validationRules" => $allValidationRules,
            "fieldResolver" => function($root, $args, $context, $info) use($container) {
                return $container[DefaultResolver::class]->resolve($root, $args, $context, $info);
            }
        ]);
    },

    BuilderInterface::class => function(ContainerInterface $container) {
        return new Builder(
            $container->getSettings()["schema"]["path"],
            $container[TypeConfigDecoratorInterface::class]
        );
    },

    SubscriptionManagerFactoryInterface::class => function(ContainerInterface $container) {
        return new SubscriptionManagerFactory(
            $container[Schema::class],
            $container[ContextInterface::class],
            $container[PromiseAdapter::class]
        );
    },

    GraphqlController::class => function(ContainerInterface $container) {
        return new GraphqlController(
            $container[ServerConfig::class]
        );
    },

    CorsMiddleware::class => function(ContainerInterface $container) {
        return new CorsMiddleware();
    },

    SubscriptionMiddleware::class => function(ContainerInterface $container) {
        return new SubscriptionMiddleware();
    },

    UploadMiddleware::class => function(ContainerInterface $container) {
        return new UploadMiddleware();
    },

    IDFactoryInterface::class => function(ContainerInterface $container) {
        return new IDFactory();
    },

    ConnectionFactoryInterface::class => function(ContainerInterface $container) {
        return new ConnectionFactory(
            $container[DataLoaderFactory::class]
        );
    },

    SubscriptionFactoryInterface::class => function(ContainerInterface $container) {
        return new SubscriptionFactory(
            $container[EventSourceInterface::class],
            $container[PromiseAdapter::class]
        );
    },

    TypeConfigDecoratorInterface::class => function(ContainerInterface $container) {
        $resolversMap = $container->getSettings()["schema"]["resolvers"];
        $resolveClass = function($className) use ($container) {
            return $container->has($className) ? $container[$className] : null;
        };

        $scalarTypeDecorator = new ScalarTypeConfigDecorator(
            $resolversMap,
            $resolveClass
        );

        $interfaceTypeDecorator = new AbstractTypeConfigDecorator(
            $resolversMap,
            $resolveClass
        );

        $objectTypeDecorator = new ObjectTypeConfigDecorator(
            $resolversMap,
            $resolveClass,
            $container[DefaultResolver::class],
            $container[IDFactoryInterface::class],
            $container[PromiseAdapter::class]
        );

        return new AggregateTypeConfigDecorator([
            $scalarTypeDecorator,
            $interfaceTypeDecorator,
            $objectTypeDecorator
        ]);
    },

    ValueResolver::class => function(ContainerInterface $container) {
        return new ValueResolver();
    },

    DateResolver::class => function(ContainerInterface $container) {
        return new DateResolver();
    },

    CursorResolver::class => function(ContainerInterface $container) {
        return new CursorResolver();
    },

    UploadResolver::class => function(ContainerInterface $container) {
        return new UploadResolver();
    },

    NodeResolver::class => function(ContainerInterface $container) {
        return new NodeResolver();
    },

    PresentationAwareTypeResolver::class => function(ContainerInterface $container) {
        return new PresentationAwareTypeResolver();
    },

    DataLoaderFactory::class => function(ContainerInterface $container) {
        return new DataLoaderFactory(
            $container[PromiseAdapter::class],
            $container[ContextInterface::class],
            $container[EventSourceInterface::class]
        );
    },

    IdentityStorageInterface::class => function(ContainerInterface $container) {
        return new IdentityStorage();
    },

    IdentityServiceInterface::class => function(ContainerInterface $container) {
        return new IdentityService(
            $container->getSettings()["jwt"]
        );
    },

    AuthenticationAdapterInterface::class => function(ContainerInterface $container) {
        return new AuthenticationAdapter(
            $container->getIntegration()->getUserRepository(),
            $container[IdentityServiceInterface::class]
        );
    },

    AuthenticationServiceInterface::class => function(ContainerInterface $container) {
        return new AuthenticationService(
            $container[IdentityStorageInterface::class],
            $container[AuthenticationAdapterInterface::class]
        );
    },

    AuthenticationMiddleware::class => function(ContainerInterface $container) {
        return new AuthenticationMiddleware(
            $container->getSettings()["jwt"],
            $container[IdentityStorageInterface::class],
            $container[IdentityServiceInterface::class],
            $container[TokenBuilderInterface::class]
        );
    },

    TokenBuilderInterface::class => function(ContainerInterface $container) {
        return new TokenBuilder(
            $container->getSettings()["jwt"]
        );
    },

    ViewerInterface::class => function(ContainerInterface $container) {
        return new Viewer(
            $container[AuthenticationServiceInterface::class]
        );
    },

    ContextInterface::class => function(ContainerInterface $container) {
        return new Context(
            $container[ViewerInterface::class]
        );
    },

    EventSourceInterface::class => function(ContainerInterface $container) {
        return new EventSource(
            $container->getIntegration()->getSubscriptionEventsRepository()
        );
    },

    // Routes

    SubscriptionController::class => function(ContainerInterface $container) {
        return new SubscriptionController(
            $container[SubscriptionManagerFactoryInterface::class],
            $container[EventSourceInterface::class],
            $container->getIntegration()->getSubscriptionEventsRepository()
        );
    },

    // Resolvers

    DefaultResolver::class => function(ContainerInterface $container) {
        return new DefaultResolver(
            $container[IDFactoryInterface::class]
        );
    },

    QueryResolver::class => function(ContainerInterface $container) {
        return new QueryResolver(
            $container[ConnectionFactoryInterface::class],
            $container->getIntegration()->getUserRepository(),
            $container->getIntegration()->getProfileRepository()
        );
    },

    UserResolver::class => function(ContainerInterface $container) {
        return new UserResolver(
            $container->getIntegration()->getUserRepository(),
            $container->getIntegration()->getFriendshipRepository(),

            $container[DataLoaderFactory::class],
            $container[ConnectionFactoryInterface::class]
        );
    },

    UserSubscriptionResolver::class => function(ContainerInterface $container) {
        return new UserSubscriptionResolver(
            $container->getIntegration()->getUserRepository(),
            $container[SubscriptionFactoryInterface::class],
            $container[DataLoaderFactory::class]
        );
    },

    UserInfoResolver::class => function(ContainerInterface $container) {
        return new UserInfoResolver(
            $container->getIntegration()->getUserRepository(),
            $container[DataLoaderFactory::class]
        );
    },

    Relay\EdgeFactory::class => function(ContainerInterface $container) {
        return new Relay\EdgeFactory(
            $container[PromiseAdapter::class]
        );
    },

    Relay\ConnectionResolver::class => function(ContainerInterface $container) {
        return new Relay\ConnectionResolver(
            $container[Relay\EdgeFactory::class]
        );
    },

    PhotoResolver::class => function(ContainerInterface $container) {
        return new PhotoResolver(
            $container->getIntegration()->getPhotoRepository(),
            $container[DataLoaderFactory::class],
            $container[ConnectionFactoryInterface::class]
        );
    },

    CommentResolver::class => function(ContainerInterface $container) {
        return new CommentResolver(
            $container->getIntegration()->getCommentRepository(),
            $container[DataLoaderFactory::class]
        );
    },

    AvatarResolver::class => function(ContainerInterface $container) {
        return new AvatarResolver(
            $container->getIntegration()->getAvatarRepository(),
            $container[DataLoaderFactory::class]
        );
    },

    AccountTypeResolver::class => function(ContainerInterface $container) {
        return new AccountTypeResolver(
            $container->getIntegration()->getProfileRepository(),
            $container[DataLoaderFactory::class]
        );
    },

    ProfileFieldResolver::class => function(ContainerInterface $container) {
        return new ProfileFieldResolver(
            $container->getIntegration()->getProfileRepository(),
            $container[DataLoaderFactory::class]
        );
    },

    ProfileFieldValueResolver::class => function(ContainerInterface $container) {
        return new ProfileFieldValueResolver(
            $container->getIntegration()->getProfileRepository(),
            $container[DataLoaderFactory::class]
        );
    },

    ProfileFieldSectionResolver::class => function(ContainerInterface $container) {
        return new ProfileFieldSectionResolver(
            $container->getIntegration()->getProfileRepository(),
            $container[DataLoaderFactory::class]
        );
    },

    ProfileResolver::class => function(ContainerInterface $container) {
        return new ProfileResolver(
            $container->getIntegration()->getProfileRepository(),
            $container[DataLoaderFactory::class]
        );
    },

    ProfileMutationResolver::class => function(ContainerInterface $container) {
        return new ProfileMutationResolver(
            $container->getIntegration()->getProfileRepository(),
            $container[DataLoaderFactory::class],
            $container[IDFactoryInterface::class]
        );
    },

    ChatResolver::class => function(ContainerInterface $container) {
        return new ChatResolver(
            $container->getIntegration()->getChatRepository(),
            $container[DataLoaderFactory::class],
            $container[ConnectionFactoryInterface::class]
        );
    },

    MessageResolver::class => function(ContainerInterface $container) {
        return new MessageResolver(
            $container->getIntegration()->getChatRepository(),
            $container[DataLoaderFactory::class]
        );
    },

    MessageMutationResolver::class => function(ContainerInterface $container) {
        return new MessageMutationResolver(
            $container->getIntegration()->getChatRepository(),
            $container[Relay\EdgeFactory::class]
        );
    },

    MessageSubscriptionResolver::class => function(ContainerInterface $container) {
        return new MessageSubscriptionResolver(
            $container->getIntegration()->getChatRepository(),
            $container[DataLoaderFactory::class],
            $container[SubscriptionFactoryInterface::class],
            $container[Relay\EdgeFactory::class]
        );
    },

    AuthMutationResolver::class => function(ContainerInterface $container) {
        return new AuthMutationResolver(
            $container[AuthenticationServiceInterface::class],
            $container[TokenBuilderInterface::class],
            $container[IdentityServiceInterface::class],
            $container->getIntegration()->getUserRepository()
        );
    },

    AvatarMutationResolver::class => function(ContainerInterface $container) {
        return new AvatarMutationResolver(
            $container->getIntegration()->getAvatarRepository()
        );
    },

    PhotoMutationResolver::class => function(ContainerInterface $container) {
        return new PhotoMutationResolver(
            $container->getIntegration()->getPhotoRepository(),
            $container[Relay\EdgeFactory::class]
        );
    },

    PhotoCommentSubscriptionResolver::class => function(ContainerInterface $container) {
        return new PhotoCommentSubscriptionResolver(
            $container->getIntegration()->getPhotoRepository(),
            $container->getIntegration()->getCommentRepository(),
            $container[SubscriptionFactoryInterface::class],
            $container[DataLoaderFactory::class]
        );
    },

    FriendshipResolver::class => function(ContainerInterface $container) {
        return new FriendshipResolver(
            $container->getIntegration()->getFriendshipRepository(),
            $container[DataLoaderFactory::class]
        );
    },

    FriendMutationResolver::class => function(ContainerInterface $container) {
        return new FriendMutationResolver(
            $container->getIntegration()->getFriendshipRepository(),
            $container[Relay\EdgeFactory::class]
        );
    },

    UserFriendsConnectionResolver::class => function(ContainerInterface $container) {
        return new UserFriendsConnectionResolver(
            $container->getIntegration()->getFriendshipRepository(),
            $container[DataLoaderFactory::class],
            $container[Relay\EdgeFactory::class]
        );
    },

    UserChatsConnectionResolver::class => function(ContainerInterface $container) {
        return new UserChatsConnectionResolver(
            $container->getIntegration()->getChatRepository(),
            $container[DataLoaderFactory::class],
            $container[Relay\EdgeFactory::class]
        );
    },

    ChatMessagesConnectionResolver::class => function(ContainerInterface $container) {
        return new ChatMessagesConnectionResolver(
            $container->getIntegration()->getChatRepository(),
            $container[DataLoaderFactory::class],
            $container[Relay\EdgeFactory::class]
        );
    },

    ChatEdgeResolver::class => function(ContainerInterface $container) {
        return new ChatEdgeResolver(
            $container[ConnectionFactoryInterface::class]
        );
    }
];
