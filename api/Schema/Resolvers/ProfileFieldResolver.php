<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Contract\Integration\ProfileRepositoryInterface;
use Iola\Api\Contract\Schema\DataLoaderFactoryInterface;
use Iola\Api\Entities\ProfileField;
use Iola\Api\Schema\EntityResolver;

class ProfileFieldResolver extends EntityResolver
{
    public function __construct(ProfileRepositoryInterface $profileRepository, DataLoaderFactoryInterface $loaderFactory)
    {
        $entityLoader = $loaderFactory->create(function ($ids) use ($profileRepository) {
            return $profileRepository->findFieldsByIds($ids);
        });

        parent::__construct($entityLoader);

        $this->addFieldResolver('section', function(ProfileField $field) {
            return $field->sectionId;
        });

        $this->addFieldResolver('configs', function(ProfileField $field) {
            return array_merge((array) $field->configs, [
                "presentation" => $field->presentation,
            ]);
        });
    }
}
