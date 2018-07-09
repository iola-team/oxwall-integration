<?php
/**
 * Created by PhpStorm.
 * User: skambalin
 * Date: 19.10.17
 * Time: 17.37
 */

namespace Everywhere\Oxwall\Integration;

use Everywhere\Api\Contract\Integration\IntegrationInterface;
use Everywhere\Oxwall\Integration\Repositories\AvatarRepository;
use Everywhere\Oxwall\Integration\Repositories\ChatRepository;
use Everywhere\Oxwall\Integration\Repositories\ProfileRepository;
use Everywhere\Oxwall\Integration\Repositories\UserRepository;
use Everywhere\Oxwall\Integration\Repositories\PhotoRepository;
use Everywhere\Oxwall\Integration\Repositories\CommentRepository;

class Integration implements IntegrationInterface
{
    public static function getTmpDir() {
        return \OW::getPluginManager()->getPlugin('esapi')->getPluginFilesDir();
    }
    
    public function getUserRepository()
    {
        return new UserRepository();
    }

    public function getPhotoRepository()
    {
        return new PhotoRepository();
    }

    public function getCommentRepository()
    {
        return new CommentRepository();
    }

    public function getAvatarRepository()
    {
        return new AvatarRepository();
    }

    public function getProfileRepository()
    {
        return new ProfileRepository();
    }

    public function getChatRepository()
    {
        return new ChatRepository();
    }
}
