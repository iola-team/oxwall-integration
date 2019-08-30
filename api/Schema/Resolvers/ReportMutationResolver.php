<?php

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Auth\Errors\PermissionError;
use Iola\Api\Schema\CompositeResolver;
use Iola\Api\Contract\Integration\ReportRepositoryInterface;
use Iola\Api\Contract\Schema\ContextInterface;

class ReportMutationResolver extends CompositeResolver
{
    public function __construct(ReportRepositoryInterface $reportRepository)
    {
        parent::__construct();

        $this->addFieldResolver("addReport", function($root, $args, ContextInterface $context) use($reportRepository) {
            $input = $args["input"];
            $userId = $input["userId"]->getId();

            if ($userId !== $context->getViewer()->getUserId()) {
                throw new PermissionError();
            }

            return $reportRepository->addReport(
                $input["contentId"]->getType(),
                $input["contentId"]->getId(),
                $userId,
                $input["reason"]
            );
        });
    }
}