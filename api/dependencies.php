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
use Everywhere\Api\Middleware\AuthMiddleware;
use Everywhere\Api\Middleware\GraphQLMiddleware;
use Everywhere\Api\Middleware\AuthenticationMiddleware;
use Everywhere\Api\Middleware\UploadMiddleware;
use Everywhere\Api\Schema\ConnectionFactory;
use Everywhere\Api\Schema\Resolvers\CursorResolver;
use Everywhere\Api\Schema\Resolvers\DateResolver;
use Everywhere\Api\Schema\Resolvers\NodeResolver;
use Everywhere\Api\Schema\RelayConnectionResolver;
use Everywhere\Api\Schema\Resolvers\UploadResolver;
use Everywhere\Api\Schema\Resolvers\UserInfoResolver;
use Everywhere\Api\Schema\TypeConfigDecorators\AggregateTypeConfigDecorator;
use Everywhere\Api\Schema\TypeConfigDecorators\InterfaceTypeConfigDecorator;
use Everywhere\Api\Schema\TypeConfigDecorators\ObjectTypeConfigDecorator;
use Everywhere\Api\Schema\TypeConfigDecorators\ScalarTypeConfigDecorator;
use Everywhere\Api\Schema\Context;
use Everywhere\Api\Schema\DataLoaderFactory;
use Everywhere\Api\Schema\Builder;
use Everywhere\Api\Schema\IDFactory;
use Everywhere\Api\Schema\Resolvers\AuthenticationResolver;
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

        $interfaceTypeDecorator = new InterfaceTypeConfigDecorator(
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
            $container->getIntegration()->getUsersRepository(),
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
            $container->getIntegration()->getUsersRepository()
        );
    },

    UserResolver::class => function(ContainerInterface $container) {
        return new UserResolver(
            $container->getIntegration()->getUsersRepository(),
            $container[DataLoaderFactory::class],
            $container[ConnectionFactoryInterface::class]
        );
    },

    UserInfoResolver::class => function(ContainerInterface $container) {
        return new UserInfoResolver(
            $container->getIntegration()->getUsersRepository(),
            $container[DataLoaderFactory::class]
        );
    },

    RelayConnectionResolver::class => function(ContainerInterface $container) {
        return new RelayConnectionResolver();
    },

    PhotoResolver::class => function(ContainerInterface $container) {
        return new PhotoResolver(
            $container->getIntegration()->getPhotoRepository(),
            $container[DataLoaderFactory::class]
        );
    },

    CommentResolver::class => function(ContainerInterface $container) {
        return new CommentResolver(
            $container->getIntegration()->getCommentsRepository(),
            $container[DataLoaderFactory::class]
        );
    },

    AvatarResolver::class => function(ContainerInterface $container) {
        return new AvatarResolver(
            $container->getIntegration()->getAvatarRepository(),
            $container[DataLoaderFactory::class]
        );
    },

    AuthenticationResolver::class => function(ContainerInterface $container) {
        return new AuthenticationResolver(
            $container[AuthenticationServiceInterface::class],
            $container[TokenBuilderInterface::class]
        );
    },
];
