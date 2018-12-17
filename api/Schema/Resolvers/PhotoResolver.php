<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Integration\PhotoRepositoryInterface;
use Everywhere\Api\Contract\Schema\ConnectionFactoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderFactoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderInterface;
use Everywhere\Api\Contract\Schema\ContextInterface;
use Everywhere\Api\Entities\Photo;
use Everywhere\Api\Schema\EntityResolver;
use GraphQL\Type\Definition\ResolveInfo;

class PhotoResolver extends EntityResolver
{
    /**
     * @var ConnectionFactoryInterface
     */
    protected $connectionFactory;

    /**
     * @var DataLoaderInterface
     */
    protected $commentsLoader;

    /**
     * @var DataLoaderInterface
     */
    protected $commentsCountsLoader;

    public function __construct(
        PhotoRepositoryInterface $photoRepository,
        DataLoaderFactoryInterface $loaderFactory,
        ConnectionFactoryInterface $connectionFactory
    ) {
        parent::__construct(
            $loaderFactory->create(function($ids, $args, $context) use($photoRepository) {
                return $photoRepository->findByIds($ids);
            })
        );

        $this->connectionFactory = $connectionFactory;

        $this->commentsLoader = $loaderFactory->create(function($ids, $args, $context) use($photoRepository) {
            return $photoRepository->findComments($ids, $args);
        });

        $this->commentsCountsLoader = $loaderFactory->create(function($ids, $args, $context) use($photoRepository) {
            return $photoRepository->countComments($ids, $args);
        });

        $this->addFieldResolver("user", function(Photo $photo) {
            return $photo->userId;
        });
    }

    /**
     * @param Photo $photo
     * @param $fieldName
     * @param $args
     * @param ContextInterface $context
     * @param $info
     *
     * @return mixed
     */
    protected function resolveField(
      $photo,
      $fieldName,
      $args,
      ContextInterface $context,
      ResolveInfo $info
    ) {
        switch ($fieldName) {
            case "comments":
                return $this->connectionFactory->create(
                    $photo,
                    $args,
                    function($args) use($photo) {
                        return $this->commentsLoader->load($photo->id, $args);
                    },
                    function($args) use($photo) {
                        return $this->commentsCountsLoader->load($photo->id, $args);
                    }
                );

            default:
                return parent::resolveField($photo, $fieldName, $args, $context, $info);
        }
    }
}
