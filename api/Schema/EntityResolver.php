<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Schema;

use Iola\Api\Contract\Entities\EntityInterface;
use Iola\Api\Contract\Schema\ContextInterface;
use Iola\Api\Contract\Schema\DataLoaderInterface;
use Iola\Api\Contract\Schema\IDObjectInterface;
use GraphQL\Error\InvariantViolation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Utils\Utils;

class EntityResolver extends CompositeResolver
{
    /**
     * @var DataLoaderInterface
     */
    protected $entityLoader;

    public function __construct(DataLoaderInterface $entityLoader, array $fieldResolvers = [])
    {
        parent::__construct($fieldResolvers);

        $this->entityLoader = $entityLoader;

        $this->addFieldResolver("id", function(EntityInterface $entity) {
            return $entity->getId();
        });
    }

    protected function isEntity($value)
    {
        return $value instanceof EntityInterface;
    }

    public function resolve($root, $args, ContextInterface $context, ResolveInfo $info)
    {
        /**
         * If $root value is null might mean that we currently processing a mutation
         */
        if ($root === null) {
            return $this->resolveField(null, $info->fieldName, $args, $context, $info);
        }

        $id = $root;
        if ($this->isEntity($root)) {
            $id = $root->getId();
            $this->entityLoader->prime($id, $root);
        } else if ($id instanceof IDObjectInterface) {
            $id = $id->getId();
        }

        return $this->entityLoader->load($id)->then(function($entity) use ($info, $args, $context) {
            if (!$this->isEntity($entity)) {
                throw new InvariantViolation(
                    'Expected an entity object but received: ' . Utils::printSafe($entity)
                );
            }

            return $this->resolveField($entity, $info->fieldName, $args, $context, $info);
        });
    }

    /**
     * @param $entity
     * @param $fieldName
     * @param $args
     * @param $context
     * @param $info
     *
     * @return null
     */
    protected function resolveField($entity, $fieldName, $args, ContextInterface $context, ResolveInfo $info)
    {
        $value = parent::resolveField($entity, $fieldName, $args, $context, $info);

        if ($value !== $this->undefined()) {
            return $value;
        }

        return isset($entity->{$fieldName})
            ? $entity->{$fieldName}
            : null;
    }
}
