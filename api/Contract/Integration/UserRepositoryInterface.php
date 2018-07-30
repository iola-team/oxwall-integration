<?php
/**
 * Created by PhpStorm.
 * User: skambalin
 * Date: 19.10.17
 * Time: 16.24
 */

namespace Everywhere\Api\Contract\Integration;

interface UserRepositoryInterface extends AuthRepositoryInterface
{
    /**
     * @param array $args
     * @return <User>
     */
    public function create($args);

    /**
     * @param array $ids
     * @return array<User>
     */
    public function findByIds($ids);

    /**
     * @param array $args
     * @return array
     */
    public function findAllIds(array $args);

    /**
     * @param array $args
     * @return array
     */
    public function countAll(array $args);

    /**
     * @param $ids
     * @param $args
     * @return mixed
     */
    public function findFriends($ids, array $args);

    /**
     * @param $ids
     * @param array $args
     * @return mixed
     */
    public function countFriends($ids, array $args);

    /**
     * @param $ids
     * @param $args
     * @return mixed
     */
    public function findPhotos($ids, array $args);

    /**
     * @param $ids
     * @param array $args
     * @return mixed
     */
    public function countPhotos($ids, array $args);

    /**
     * @param $ids
     * @param array $args
     * @return mixed
     */
    public function findAvatars($ids, array $args);

    /**
     * @param mixed[] $ids
     * @param mixed[] $args
     *
     * @return string[]
     */
    public function findChat($ids, array $args);

    /**
     * @param mixed[] $ids
     * @param mixed[] $args
     *
     * @return string[]
     */
    public function findChats($ids, array $args);

    /**
     * @param mixed[] $ids
     * @param mixed[] $args
     * @return int[]
     */
    public function countChats($ids, array $args);

    /**
     * @param $ids
     * @param array $args
     * @return mixed
     */
    public function getInfo($ids, array $args);
}
