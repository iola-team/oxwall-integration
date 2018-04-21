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

    public function countAll();

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
     * @param $ids
     * @param array $args
     * @return mixed
     */
    public function getInfo($ids, array $args);
}
