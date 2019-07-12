<?php

namespace Iola\Api\Contract\Integration;

interface AvatarRepositoryInterface
{
    public function findByIds($ids);

    public function getUrls($ids, array $args);

    public function addAvatar(array $args);

    public function deleteAvatar(array $args);
}
