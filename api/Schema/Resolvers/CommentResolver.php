<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

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
