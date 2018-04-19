<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Schema\ContextInterface;
use Everywhere\Api\Schema\CompositeResolver;
use Psr\Http\Message\UploadedFileInterface;

class FileMutationResolver extends CompositeResolver
{
    public function __construct()
    {
        parent::__construct([
            "uploadFiles" => [$this, "uploadFiles"]
        ]);
    }

    public function uploadFiles($root, $args, ContextInterface $context)
    {
        $out = [];

        /**
         * @var $file UploadedFileInterface
         */
        foreach ($args["files"] as $file) {
            $out[] = $file->getStream()->read(1000);
        }

        return $out;
    }
}
