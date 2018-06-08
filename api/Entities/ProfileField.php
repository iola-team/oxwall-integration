<?php

namespace Everywhere\Api\Entities;

class ProfileField extends AbstractEntity
{
    const PRESENTATION_TEXT = "TEXT";
    const PRESENTATION_DATE = "DATE";
    const PRESENTATION_SELECT = "SELECT";
    const PRESENTATION_SWITCH = "SWITCH";
    const PRESENTATION_RANGE = "RANGE";

    const TEXT_FORMAT_URL = "URL";
    const TEXT_FORMAT_EMAIL = "EMAIL";

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $presentation;

    /**
     * @var boolean
     */
    public $isRequired;

    /**
     * @var mixed
     */
    public $sectionId;

    /**
     * @var array|null
     */
    public $configs;
}
