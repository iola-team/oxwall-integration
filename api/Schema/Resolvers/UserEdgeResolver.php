<?php
/**
 * Created by PhpStorm.
 * User: skambalin
 * Date: 19.10.17
 * Time: 15.16
 */

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Integration\UsersRepositoryInterface;
use Everywhere\Api\Contract\Schema\ContextInterface;
use Everywhere\Api\Contract\Schema\ResolverInterface;
use Everywhere\Api\Entities\User;
use Everywhere\Api\Schema\CompositeResolver;
use GraphQL\Type\Definition\ResolveInfo;

class UserEdgeResolver extends CompositeResolver
{

    protected $userResolver;

    public function __construct(UserResolver $usersResolver)
    {
        parent::__construct();

        $this->userResolver = $usersResolver;

        $this->addFieldResolver("node", function($root) {
            return $root;
        });

        $this->addFieldResolver("cursor", function($root) {
            return 'cursor';
        });
    }

}
