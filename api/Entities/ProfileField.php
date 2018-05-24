<?php

namespace Everywhere\Api\Entities;

class ProfileField extends AbstractEntity
{
    const PRESENTATION_TEXT = "TEXT";
    const PRESENTATION_TEXTAREA = "TEXTAREA";
    const PRESENTATION_PASSWORD = "PASSWORD";
    const PRESENTATION_URL = "URL";
    const PRESENTATION_DATE = "DATE";
    const PRESENTATION_SINGLE_CHOICE = "SINGLE_CHOICE";
    const PRESENTATION_MULTI_CHOICE = "MULTI_CHOICE";
    const PRESENTATION_SWITCH = "SWITCH";
    const PRESENTATION_RANGE = "RANGE";

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
     * @var array
     */
    public $configs;
}
