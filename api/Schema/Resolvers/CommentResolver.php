<?php
namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Integration\CommentRepositoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderFactoryInterface;
use Everywhere\Api\Entities\Comment;
use Everywhere\Api\Schema\EntityResolver;

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
