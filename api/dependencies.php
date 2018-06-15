<?php
namespace Everywhere\Api;

use Everywhere\Api\Auth\AuthenticationAdapter;
use Everywhere\Api\Auth\AuthenticationService;
use Everywhere\Api\Auth\IdentityService;
use Everywhere\Api\Auth\IdentityStorage;
use Everywhere\Api\Auth\TokenBuilder;
use Everywhere\Api\Contract\App\ContainerInterface;
use Everywhere\Api\Contract\Auth\AuthenticationAdapterInterface;
use Everywhere\Api\Contract\Auth\AuthenticationServiceInterface;
use Everywhere\Api\Contract\Auth\IdentityServiceInterface;
use Everywhere\Api\Contract\Auth\IdentityStorageInterface;
use Everywhere\Api\Contract\Auth\TokenBuilderInterface;
use Everywhere\Api\Contract\Schema\ConnectionFactoryInterface;
use Everywhere\Api\Contract\Schema\ContextInterface;
use Everywhere\Api\Contract\Schema\DataLoaderFactoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderInterface;
use Everywhere\Api\Contract\Schema\IDFactoryInterface;
use Everywhere\Api\Contract\Schema\ViewerInterface;
use Everywhere\Api\Middleware\GraphQLMiddleware;
use Everywhere\Api\Middleware\AuthenticationMiddleware;
use Everywhere\Api\Middleware\UploadMiddleware;
use Everywhere\Api\Schema\ConnectionFactory;
use Everywhere\Api\Schema\Relay;
use Everywhere\Api\Schema\Resolvers\AccountTypeResolver;
use Everywhere\Api\Schema\Resolvers\AvatarMutationResolver;
use Everywhere\Api\Schema\Resolvers\ChatResolver;
use Everywhere\Api\Schema\Resolvers\CursorResolver;
use Everywhere\Api\Schema\Resolvers\DateResolver;
use Everywhere\Api\Schema\Resolvers\MessageResolver;
use Everywhere\Api\Schema\Resolvers\NodeResolver;
use Everywhere\Api\Schema\Resolvers\PhotoMutationResolver;
use Everywhere\Api\Schema\Resolvers\PresentationAwareTypeResolver;
use Everywhere\Api\Schema\Resolvers\ProfileFieldResolver;
use Everywhere\Api\Schema\Resolvers\ProfileFieldSectionResolver;
use Everywhere\Api\Schema\Resolvers\ProfileFieldValueResolver;
use Everywhere\Api\Schema\Resolvers\ProfileMutationResolver;
use Everywhere\Api\Schema\Resolvers\UploadResolver;
use Everywhere\Api\Schema\Resolvers\UserInfoResolver;
use Everywhere\Api\Schema\Resolvers\ProfileResolver;
use Everywhere\Api\Schema\Resolvers\ValueResolver;
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
use GraphQL\Server\ServerConfig;
use GraphQL\Executor\Promise\PromiseAdapter;
use Overblog\DataLoader\Promise\Adapter\Webonyx\GraphQL\SyncPromiseAdapter;
use Everywhere\Api\Schema\Resolvers\QueryResolver;
use Everywhere\Api\Schema\Resolvers\UserResolver;
use Everywhere\Api\Schema\Resolvers\PhotoResolver;
use Everywhere\Api\Schema\Resolvers\CommentResolver;

return [
    PromiseAdapter::class => function() {
        return new SyncPromiseAdapter();
    },

    ServerConfig::class => function(ContainerInterface $container) {
        return ServerConfig::create([
            "debug" => true,
            "queryBatching" => true,
            "context" => $container[ContextInterface::class],
            "schema" => $container[BuilderInterface::class]->build(),
            "promiseAdapter" => $container[PromiseAdapter::class],
        ]);
    },

    BuilderInterface::class => function(ContainerInterface $container) {
        return new Builder(
            $container->getSettings()["schema"]["path"],
            $container[TypeConfigDecoratorInterface::class]
        );
    },

    GraphQLMiddleware::class => function(ContainerInterface $container) {
        return new GraphQLMiddleware(
            $container[ServerConfig::class]
        );
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
            $container[ContextInterface::class]
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

    // Resolvers

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
            $container[DataLoaderFactory::class],
            $container[ConnectionFactoryInterface::class]
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
            $container[DataLoaderFactory::class]
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

    ProfileMutationResolver::class => function(ContainerInterface $container) {
        return new ProfileMutationResolver(
            $container->getIntegration()->getProfileRepository(),
            $container[DataLoaderFactory::class],
            $container[IDFactoryInterface::class]
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
            $container->getIntegration()->getPhotoRepository()
        );
    },
];
