<?php
/**
 * Created by PhpStorm.
 * User: skambalin
 * Date: 21.10.17
 * Time: 17.00
 */

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Integration\PhotoRepositoryInterface;
use Everywhere\Api\Contract\Schema\ContextInterface;
use Everywhere\Api\Contract\Schema\DataLoaderFactoryInterface;
use Everywhere\Api\Entities\Photo;
use Everywhere\Api\Schema\EntityResolver;
use GraphQL\Executor\Promise\Promise;

class PhotoResolver extends EntityResolver
{
    public function __construct(PhotoRepositoryInterface $photoRepository, DataLoaderFactoryInterface $loaderFactory)
    {
        $entityLoader = $loaderFactory->create(function ($ids) use ($photoRepository) {
            return $photoRepository->findByIds($ids);
        });

        $commentsLoader = $loaderFactory->create(function($ids, $args) use($photoRepository) {
            return $photoRepository->findComments($ids, $args);
        }, []);

        $resolvers = [
            "owner" => function(Photo $photo) {
                return $photo->userId;
            },

            "comments" => function(Photo $photo, $args) use($commentsLoader) {
                return $commentsLoader->load($photo->id, $args);
            }
        ];

        parent::__construct($entityLoader, $resolvers);
    }
}
