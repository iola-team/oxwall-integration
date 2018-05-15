<?php
/**
 * Created by PhpStorm.
 * User: skambalin
 * Date: 21.10.17
 * Time: 17.03
 */

namespace Everywhere\Api\Contract\Integration;


use Everywhere\Api\Entities\Photo;

interface PhotoRepositoryInterface
{
    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids);

    /**
     * @param $ids
     * @return mixed
     */
    public function findComments($ids, array $args);

    /**
     * @param $ids
     * @return void
     */
    public function deleteByIds($ids);

    /**
     *
     * @param $userId
     * @param array $input
     * @return Photo
     */
    public function addUserPhoto($userId, array $input);
}
