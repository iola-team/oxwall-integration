<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Integration\ChatRepositoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderFactoryInterface;
use Everywhere\Api\Entities\Message;
use Everywhere\Api\Schema\Relay\EntityEdgeFactory;
use GraphQL\Executor\Promise\PromiseAdapter;

class MessageEdgeFactory extends EntityEdgeFactory
{
    protected $messageLoader;

    public function __construct(
        ChatRepositoryInterface $chatRepository,
        DataLoaderFactoryInterface $loaderFactory,
        PromiseAdapter $promiseAdapter
    ) {
        $entityLoader = $loaderFactory->create(function($ids) use($chatRepository) {
            return $chatRepository->findMessagesByIds($ids);
        });

        parent::__construct($entityLoader, $promiseAdapter);
    }

    /**
     * @param Message $node
     * @param mixed[] $fromCursor
     * @param int $offset
     *
     * @return mixed[]
     */
    protected function buildCursor($node, $fromCursor, $offset)
    {
        return [
            "messageId" => $node->id
        ];
    }
}
