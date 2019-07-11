<?php

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Contract\Integration\CommentRepositoryInterface;
use Iola\Api\Contract\Schema\DataLoaderFactoryInterface;
use Iola\Api\Entities\Comment;
use Iola\Api\Schema\EntityResolver;

class CommentResolver extends EntityResolver
{
    public function __construct(CommentRepositoryInterface $commentRepository, DataLoaderFactoryInterface $loaderFactory)
    {
        $entityLoader = $loaderFactory->create(function ($ids) use ($commentRepository) {
            return $commentRepository->findByIds($ids);
        });

        parent::__construct($entityLoader);

        $this->addFieldResolver("user", function(Comment $comment) {
            return $comment->userId;
        });
    }
}
