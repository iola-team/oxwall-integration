<?php

namespace Everywhere\Api\Schema\TypeConfigDecorators;

use Everywhere\Api\Contract\Entities\EntityInterface;
use Everywhere\Api\Contract\Schema\IDFactoryInterface;
use Everywhere\Api\Contract\Schema\IDObjectInterface;
use Everywhere\Api\Contract\Schema\ObjectTypeResolverInterface;
use Everywhere\Api\Contract\Schema\TypeConfigDecoratorInterface;
use Everywhere\Api\Schema\AbstractTypeConfigDecorator as TypeConfigDecorator;
use GraphQL\Executor\Promise\PromiseAdapter;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\WrappingType;
use GraphQL\Utils\Utils;

class ObjectTypeConfigDecorator extends TypeConfigDecorator
{
    protected $resolversMap;

    /**
     * @var callable
     */
    protected $getResolver;

    /**
     * @var ObjectTypeResolverInterface
     */
    protected $defaultResolver;

    /**
     * @var PromiseAdapter
     */
    protected $promiseAdapter;

    /**
     * @var IDFactoryInterface
     */
    protected $idFactory;

    public function __construct(
        array $resolversMap,
        callable $getResolver,
        ObjectTypeResolverInterface $defaultResolver,
        IDFactoryInterface $idFactory,
        PromiseAdapter $promiseAdapter
    ) {
        $this->resolversMap = $resolversMap;
        $this->getResolver = $getResolver;
        $this->defaultResolver = $defaultResolver;
        $this->promiseAdapter = $promiseAdapter;
        $this->idFactory = $idFactory;
    }

    protected function getResolvers($typeName, $fieldName)
    {
        $resolvers = [];

        if (isset($this->resolversMap[$typeName . "." . $fieldName])) {
            $resolvers = $this->resolversMap[$typeName . "." . $fieldName];
        } else if(isset($this->resolversMap[$typeName])) {
            $resolvers = $this->resolversMap[$typeName];
        }

        $resolvers = is_array($resolvers) ? $resolvers : [ $resolvers ];

        return array_map($this->getResolver, $resolvers);
    }

    protected function getFinalType($type)
    {
        return Type::getNamedType($type);
    }

    protected function normalizeID($value)
    {
        $toIDObject = function($id) {
            return $id instanceof IDObjectInterface
                ? $id
                : $this->idFactory->createFromGlobalId($id);
        };

        return is_array($value)
            ? array_map($toIDObject, $value)
            : $toIDObject($value);
    }

    protected function normalizeArgument($value, $configs, ResolveInfo $info)
    {
        $finalType = $this->getFinalType($configs["type"]);

        if ($finalType instanceof IDType) {
            $value = $this->normalizeID($value);
        }

        if ($finalType instanceof InputObjectType) {
            foreach ($value as $name => $fieldValue) {
                $filedType = $finalType->getField($name)->getType();
                if ($this->getFinalType($filedType) instanceof IDType) {
                    $value[$name] = $this->normalizeID($fieldValue);
                }
            }
        }

        return $value;
    }

    /**
     * Tries to resolve global ID value based on local ID and parent type
     * TODO: try to merge this logic with DefaultResolver logic somehow
     *
     * @param mixed $value
     * @param ResolveInfo $info
     * 
     * @return mixed|IDObjectInterface
     */
    protected function normalizeValue($value, ResolveInfo $info)
    {
        $finalType = $this->getFinalType($info->returnType);

        if ($finalType instanceof IDType
            && !$value instanceof IDObjectInterface
            && !is_array($value)
        ) {
            return $this->idFactory->create($info->parentType->name, $value);
        }

        return $value;
    }

    protected function normalizeRoot($value, ResolveInfo $info)
    {
        return $value;
    }

    private function decorateField($parentTypeName, $fieldName, $configs) {
        $undefined = Utils::undefined();
        $resolvers = $this->getResolvers($parentTypeName, $fieldName);

        if (empty($resolvers)) {
            return $configs;
        }

        $configs["resolve"] = function($root, $args, $context, ResolveInfo $info) use (
            $undefined, $configs, $resolvers, $parentTypeName, $fieldName
        ) {
            $outPromise = $this->promiseAdapter->createFulfilled($undefined);

            $normalizedArgs = [];
            foreach ($args as $name => $value) {
                $argConfig = $configs["args"][$name];

                $normalizedArgs[$name] = $this->normalizeArgument($value, $argConfig, $info);
            }

            $normalizedRoot = $this->normalizeRoot($root, $info);

            /**
             * @var $resolver ObjectTypeResolverInterface
             */
            foreach ($resolvers as $resolver) {
                if (!$resolver || !$resolver instanceof ObjectTypeResolverInterface) {
                    throw new InvariantViolation(
                        "Resolver for `" . Utils::printSafe($info->parentType) . "` type was not found or invalid"
                    );
                }

                $valuePromise = $this->promiseAdapter->createFulfilled(
                    $resolver->resolve($normalizedRoot, $normalizedArgs, $context, $info)
                );

                $outPromise = $outPromise->then(function($oldValue) use ($undefined, $valuePromise) {
                    return $valuePromise->then(function($newValue) use ($undefined, $oldValue) {
                        return $newValue === $undefined ? $oldValue : $newValue;
                    });
                });
            }

            return $outPromise->then(
                function($value) use($undefined, $normalizedRoot, $normalizedArgs, $context, $info) {

                    /**
                     * Use default resolver If the value returned from field resolvers is undefined
                     */
                    $value = $value === $undefined
                        ? $this->defaultResolver->resolve($normalizedRoot, $normalizedArgs, $context, $info)
                        : $value;

                    if ($value !== null) {
                        return $this->normalizeValue($value, $info);
                    }

                    return $value;
                }
            );
        };

        return $configs;
    }

    public function decorate(array $typeConfig)
    {
        if ($this->getKind($typeConfig) !== NodeKind::OBJECT_TYPE_DEFINITION) {
            return $typeConfig;
        }

        $typeConfig["fields"] = function() use ($typeConfig) {
            $fields = is_callable($typeConfig["fields"]) ? $typeConfig["fields"]() : $typeConfig["fields"];

            $out = [];
            foreach ($fields as $name => $config) {
                $out[$name] = $this->decorateField($typeConfig["name"], $name, $config);
            }

            return $out;
        };

        return $typeConfig;
    }
}
