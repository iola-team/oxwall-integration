<?php
namespace Iola\Api;

use Iola\Api\App\EventManager;
use Iola\Api\Auth\AuthenticationAdapter;
use Iola\Api\Auth\AuthenticationService;
use Iola\Api\Auth\IdentityService;
use Iola\Api\Auth\IdentityStorage;
use Iola\Api\Auth\TokenBuilder;
use Iola\Api\Contract\App\ContainerInterface;
use Iola\Api\Contract\App\EventManagerInterface;
use Iola\Api\Contract\Auth\AuthenticationAdapterInterface;
use Iola\Api\Contract\Auth\AuthenticationServiceInterface;
use Iola\Api\Contract\Auth\IdentityServiceInterface;
use Iola\Api\Contract\Auth\IdentityStorageInterface;
use Iola\Api\Contract\Auth\TokenBuilderInterface;
use Iola\Api\Contract\Integration\EventSourceInterface;
use Iola\Api\Contract\Schema\ConnectionFactoryInterface;
use Iola\Api\Contract\Schema\ContextInterface;
use Iola\Api\Contract\Schema\IDFactoryInterface;
use Iola\Api\Contract\Schema\SubscriptionFactoryInterface;
use Iola\Api\Contract\Schema\ViewerInterface;
use Iola\Api\Contract\Subscription\SubscriptionManagerFactoryInterface;
use Iola\Api\Controllers\GraphqlController;
use Iola\Api\Controllers\SubscriptionController;
use Iola\Api\Integration\EventSource;
use Iola\Api\Middleware\AuthenticationMiddleware;
use Iola\Api\Middleware\SubscriptionMiddleware;
use Iola\Api\Middleware\UploadMiddleware;
use Iola\Api\Middleware\CorsMiddleware;
use Iola\Api\Schema\ConnectionFactory;
use Iola\Api\Schema\Relay;
use Iola\Api\Schema\Resolvers\AccountTypeResolver;
use Iola\Api\Schema\Resolvers\AvatarMutationResolver;
use Iola\Api\Schema\Resolvers\ChatResolver;
use Iola\Api\Schema\Resolvers\CursorResolver;
use Iola\Api\Schema\Resolvers\DateResolver;
use Iola\Api\Schema\Resolvers\MessageMutationResolver;
use Iola\Api\Schema\Resolvers\MessageResolver;
use Iola\Api\Schema\Resolvers\MessageSubscriptionResolver;
use Iola\Api\Schema\Resolvers\NodeResolver;
use Iola\Api\Schema\Resolvers\PhotoMutationResolver;
use Iola\Api\Schema\Resolvers\PhotoCommentSubscriptionResolver;
use Iola\Api\Schema\Resolvers\PresentationAwareTypeResolver;
use Iola\Api\Schema\Resolvers\ProfileFieldResolver;
use Iola\Api\Schema\Resolvers\ProfileFieldSectionResolver;
use Iola\Api\Schema\Resolvers\ProfileFieldValueResolver;
use Iola\Api\Schema\Resolvers\ProfileMutationResolver;
use Iola\Api\Schema\Resolvers\UploadResolver;
use Iola\Api\Schema\Resolvers\UserInfoResolver;
use Iola\Api\Schema\Resolvers\UserMutationResolver;
use Iola\Api\Schema\Resolvers\ProfileResolver;
use Iola\Api\Schema\Resolvers\ValueResolver;
use Iola\Api\Schema\SubscriptionFactory;
use Iola\Api\Schema\TypeConfigDecorators\AggregateTypeConfigDecorator;
use Iola\Api\Schema\TypeConfigDecorators\AbstractTypeConfigDecorator;
use Iola\Api\Schema\TypeConfigDecorators\ObjectTypeConfigDecorator;
use Iola\Api\Schema\TypeConfigDecorators\ScalarTypeConfigDecorator;
use Iola\Api\Schema\Context;
use Iola\Api\Schema\DataLoaderFactory;
use Iola\Api\Schema\Builder;
use Iola\Api\Schema\IDFactory;
use Iola\Api\Schema\Resolvers\AuthMutationResolver;
use Iola\Api\Schema\Resolvers\AvatarResolver;
use Iola\Api\Contract\Schema\BuilderInterface;
use Iola\Api\Contract\Schema\TypeConfigDecoratorInterface;
use Iola\Api\Schema\Viewer;
use Iola\Api\Subscription\SubscriptionManagerFactory;
use GraphQL\Server\ServerConfig;
use GraphQL\Executor\Promise\PromiseAdapter;
use GraphQL\Type\Schema;
use Overblog\DataLoader\Promise\Adapter\Webonyx\GraphQL\SyncPromiseAdapter;
use Iola\Api\Schema\Resolvers\QueryResolver;
use Iola\Api\Schema\Resolvers\UserResolver;
use Iola\Api\Schema\Resolvers\UserSubscriptionResolver;
use Iola\Api\Schema\Resolvers\PhotoResolver;
use Iola\Api\Schema\Resolvers\CommentResolver;
use Iola\Api\Schema\Resolvers\FriendMutationResolver;
use Iola\Api\Schema\Resolvers\FriendshipResolver;
use Iola\Api\Schema\DefaultResolver;
use Iola\Api\Schema\Resolvers\UserFriendsConnectionResolver;
use Iola\Api\Schema\Resolvers\UserChatsConnectionResolver;
use Iola\Api\Schema\Resolvers\ChatMessagesConnectionResolver;
use Iola\Api\Schema\Resolvers\ChatEdgeResolver;
use GraphQL\Validator\DocumentValidator;
use GraphQL\Validator\Rules\NoUnusedVariables;
use Iola\Api\Middleware\SessionMiddleware;
use Iola\Api\Schema\Resolvers\FriendshipSubscriptionResolver;
use Iola\Api\Middleware\RequestTrackingMiddleware;
use Iola\Api\Schema\Resolvers\BlockMutationResolver;
use Iola\Api\Schema\Resolvers\ReportMutationResolver;

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
        return new SubscriptionManagerFactory($container[ServerConfig::class]);
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

    SessionMiddleware::class => function(ContainerInterface $container) {
        return new SessionMiddleware(
            $container[AuthenticationServiceInterface::class],
            $container->getIntegration()->getUserRepository()
        );
    },

    RequestTrackingMiddleware::class => function(ContainerInterface $container) {
        return new RequestTrackingMiddleware(
            $container[ContextInterface::class],
            $container[EventManagerInterface::class]
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
            $container->getIntegration()->getConfigRepository(),
            $container->getIntegration()->getUserRepository(),
            $container->getIntegration()->getProfileRepository()
        );
    },

    UserResolver::class => function(ContainerInterface $container) {
        return new UserResolver(
            $container->getIntegration()->getUserRepository(),
            $container->getIntegration()->getBlockRepository(),

            $container[DataLoaderFactory::class],
            $container[ConnectionFactoryInterface::class]
        );
    },

    UserSubscriptionResolver::class => function(ContainerInterface $container) {
        return new UserSubscriptionResolver(
            $container[SubscriptionFactoryInterface::class]
        );
    },

    UserInfoResolver::class => function(ContainerInterface $container) {
        return new UserInfoResolver(
            $container->getIntegration()->getUserRepository(),
            $container[DataLoaderFactory::class]
        );
    },

    UserMutationResolver::class => function(ContainerInterface $container) {
        return new UserMutationResolver(
            $container->getIntegration()->getUserRepository()
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
            $container->getIntegration()->getBlockRepository(),
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

    FriendshipSubscriptionResolver::class => function(ContainerInterface $container) {
        return new FriendshipSubscriptionResolver(
            $container->getIntegration()->getFriendshipRepository(),
            $container[SubscriptionFactoryInterface::class],
            $container[Relay\EdgeFactory::class]
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
    },

    ReportMutationResolver::class => function(ContainerInterface $container) {
        return new ReportMutationResolver(
            $container->getIntegration()->getReportRepository()
        );
    },

    BlockMutationResolver::class => function(ContainerInterface $container) {
        return new BlockMutationResolver(
            $container->getIntegration()->getBlockRepository()
        );
    }    
];
