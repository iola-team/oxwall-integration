<?php
/**
 * Created by PhpStorm.
 * User: skambalin
 * Date: 19.10.17
 * Time: 15.16
 */

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Integration\ProfileRepositoryInterface;
use Everywhere\Api\Contract\Integration\UserRepositoryInterface;
use Everywhere\Api\Contract\Schema\ConnectionFactoryInterface;
use Everywhere\Api\Contract\Schema\ContextInterface;
use Everywhere\Api\Contract\Schema\DataLoaderFactoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderInterface;
use Everywhere\Api\Entities\ProfileField;
use Everywhere\Api\Entities\User;
use Everywhere\Api\Schema\CompositeResolver;
use GraphQL\Type\Definition\ResolveInfo;

class UserProfileResolver extends CompositeResolver
{
    /**
     * @var DataLoaderInterface
     */
    protected $valuesLoader;

    /**
     * @var DataLoaderInterface
     */
    protected $fieldsLoader;

    public function __construct(
        ProfileRepositoryInterface $profileRepository,
        DataLoaderFactoryInterface $loaderFactory
    ) {
        $this->fieldsLoader = $loaderFactory->create(function($ids, $args) use ($profileRepository) {
            return $profileRepository->findFieldIdsByAccountTypeIds($ids, $args);
        });

        $this->valuesLoader = $loaderFactory->create(function($ids, $args) use ($profileRepository) {
            $fields = $args["fields"];

            return $profileRepository->getFieldValuesByUserIds($ids, $fields);
        });

        parent::__construct([
            "accountType" => function(User $user) {
                return $user->accountTypeId;
            },
            "values" => [$this, "resolveFieldList"]
        ]);
    }

    protected function resolveValues(User $user, $fields)
    {
        return $this->valuesLoader->load($user->getId(), [
            "fields" => $fields
        ]);
    }

    public function resolveFieldList(User $user, $args)
    {
        return $this->fieldsLoader->load($user->accountTypeId, $args)->then(function($fields) use ($user) {
            $out = [];

            foreach ($fields as $fieldId) {
                $out[] = [
                    "field" => $fieldId,
                    "value" => function() use ($user, $fields, $fieldId) {
                        return $this->resolveValues($user, $fields)->then(function($values) use($fieldId) {
                            return empty($values[$fieldId]) ? null : $values[$fieldId];
                        });
                    }
                ];
            }

            return $out;
        });
    }
}
