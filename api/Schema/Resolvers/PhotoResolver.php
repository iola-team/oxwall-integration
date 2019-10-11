<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Contract\Integration\PhotoRepositoryInterface;
use Iola\Api\Contract\Schema\ConnectionFactoryInterface;
use Iola\Api\Contract\Schema\DataLoaderFactoryInterface;
use Iola\Api\Contract\Schema\DataLoaderInterface;
use Iola\Api\Entities\Photo;
use Iola\Api\Schema\EntityResolver;

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

    /**
     * @var DataLoaderInterface
     */
    protected $urlLoader;

    public function __construct(
        PhotoRepositoryInterface $photoRepository,
        DataLoaderFactoryInterface $loaderFactory,
        ConnectionFactoryInterface $connectionFactory
    ) {
        parent::__construct(
            $loaderFactory->create(function($ids) use($photoRepository) {
                return $photoRepository->findByIds($ids);
            })
        );

        $this->connectionFactory = $connectionFactory;
        $this->commentsLoader = $loaderFactory->create(function($ids, $args) use($photoRepository) {
            return $photoRepository->findComments($ids, $args);
        });

        $this->commentsCountsLoader = $loaderFactory->create(function($ids, $args) use($photoRepository) {
            return $photoRepository->countComments($ids, $args);
        });

        $this->urlLoader = $loaderFactory->create(function($ids, $args) use($photoRepository) {
            return $photoRepository->getUrls($ids, $args);
        });

        $this->isBlockedLoader = $loaderFactory->create(function($ids, $args) use($blockRepository) {
            return $blockRepository->hasBlockedUser($ids, $args["for"]->getId());
        });

        // Resolvers

        $this->addFieldResolver("user", function(Photo $photo) {
            return $photo->userId;
        });

        $this->addFieldResolver("url", function(Photo $photo, array $args) {
            return $this->urlLoader->load($photo->id, $args);
        });

        $this->addFieldResolver("comments", function(Photo $photo, array $args) {
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
        });
    }
}
