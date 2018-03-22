<?php

namespace Everywhere\Api\Schema\TypeConfigDecorators;

use Everywhere\Api\Contract\Entities\EntityInterface;
use Everywhere\Api\Contract\Schema\IDFactoryInterface;
use Everywhere\Api\Contract\Schema\IDObjectInterface;
use Everywhere\Api\Contract\Schema\ObjectTypeResolverInterface;
use Everywhere\Api\Contract\Schema\TypeConfigDecoratorInterface;
use Everywhere\Api\Schema\AbstractTypeConfigDecorator;
use GraphQL\Executor\Promise\PromiseAdapter;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\WrappingType;
use GraphQL\Utils\Utils;

class ObjectTypeConfigDecorator extends AbstractTypeConfigDecorator
{
    protected $resolversMap;

    /**
     * @var callable
     */
    protected $getResolver;

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
        IDFactoryInterface $idFactory,
        PromiseAdapter $promiseAdapter
    ) {
        $this->resolversMap = $resolversMap;
        $this->getResolver = $getResolver;
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

    protected function normalizeArgument($value, $configs)
    {
        $finalType = $this->getFinalType($configs["type"]);

        if ($finalType instanceof IDType) {
            $toIDObject = function($id) {
                return $this->idFactory->createFromGlobalId($id);
            };

            $value = is_array($value)
                ? array_map($toIDObject, $value)
                : $toIDObject($value);
        }

        return $value;
    }

    protected function normalizeValue($value, ResolveInfo $info)
    {
        $finalType = $this->getFinalType($info->returnType);

        if ($finalType instanceof ObjectType) {
            $toIDObject = function($id) use ($finalType) {
                return $this->idFactory->create($finalType->name, $id);
            };

            return is_array($value)
                ? array_map($toIDObject, $value)
                : $toIDObject($value);
        }

        if ($finalType instanceof IDType) {
            return $this->idFactory->create($info->parentType->name, $value);
        }

        return $value;
    }

    protected function normalizeRoot($value)
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

                $normalizedArgs[$name] = $this->normalizeArgument($value, $argConfig);
            }

            $normalizedRoot = $this->normalizeRoot($root);

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

            return $outPromise->then(function($value) use($undefined, $info) {
                if ($value !== $undefined) {
                    return $this->normalizeValue($value, $info);
                }

                return $value;
            });
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
