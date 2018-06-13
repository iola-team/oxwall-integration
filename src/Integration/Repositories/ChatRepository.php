<?php

namespace Everywhere\Oxwall\Integration\Repositories;

use Everywhere\Api\Contract\Integration\ChatRepositoryInterface;
use Everywhere\Api\Entities\Chat;
use Everywhere\Api\Entities\User;

class ChatRepository implements ChatRepositoryInterface
{
    public function findChatsByIds($ids)
    {
        $out = [];
        foreach ($ids as $id) {
            $chat = new Chat();
            $chat->userId = 1;

            $out[$id] = $chat;
        }

        return $out;
    }
}
