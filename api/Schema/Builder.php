<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Schema;

use Iola\Api\Contract\Schema\BuilderInterface;
use Iola\Api\Contract\Schema\TypeConfigDecoratorInterface;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;

class Builder implements BuilderInterface
{
    protected $path;

    /**
     * @var TypeConfigDecoratorInterface
     */
    protected $typeConfigDecorator;

    public function __construct($schemaPath, TypeConfigDecoratorInterface $typeConfigDecorator)
    {
        $this->path = $schemaPath;
        $this->typeConfigDecorator = $typeConfigDecorator;
    }

    /**
     * @return Schema
     */
    public function build()
    {
        $typeConfigDecorator = $this->typeConfigDecorator;
        $schemaContent = file_get_contents($this->path);

        return BuildSchema::build($schemaContent, function($typeConfig) use ($typeConfigDecorator) {
            return $typeConfigDecorator->decorate($typeConfig);
        });
    }
}