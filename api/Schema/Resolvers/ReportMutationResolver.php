<?php

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Schema\CompositeResolver;
use Iola\Api\Contract\Integration\ReportRepositoryInterface;

class ReportMutationResolver extends CompositeResolver
{
    public function __construct(ReportRepositoryInterface $reportRepository)
    {
        parent::__construct();

        $this->addFieldResolver("addReport", function($root, $args) use($reportRepository) {
            $input = $args["input"];

            return $reportRepository->addReport(
                $input["contentId"]->getType(),
                $input["contentId"]->getId(),
                $input["userId"]->getId(),
                $input["reason"]
            );
        });
    }
}