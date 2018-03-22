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
use Everywhere\Api\Contract\Schema\DataLoaderInterface;
use Everywhere\Api\Contract\Schema\ResolverInterface;
use Everywhere\Api\Entities\User;
use Everywhere\Api\Schema\CompositeResolver;
use GraphQL\Type\Definition\ResolveInfo;

class UserConnectionResolver extends CompositeResolver
{
    public function __construct()
    {
        parent::__construct();

        $this->addFieldResolver("totalCount", function() {
            return 10;
        });

        $this->addFieldResolver("edges", function($list) {
            return $list;
        });

        $this->addFieldResolver("pageInfo", function() {
            return [
                "hasNextPage" => true,
                "hasPreviousPage" => false,
                "startCursor" => "startCursor",
                "endCursor" => "endCursor"
            ];
        });
    }
}
