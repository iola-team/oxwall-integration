<?php

namespace Everywhere\Api\Entities;

class ProfileFieldValue extends AbstractEntity
{
    /**
     * @var string
     */
    public $fieldId;

    /**
     * @var mixed
     */
    public $value;
}
