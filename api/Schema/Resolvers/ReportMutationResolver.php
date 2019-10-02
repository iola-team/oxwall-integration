<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

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