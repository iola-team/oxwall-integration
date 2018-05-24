<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Entities\ProfileField;

trait ProfileFiledValuePropertiesTrait
{
    private $valueProps = [
        "stringValue" => [
            ProfileField::PRESENTATION_TEXT,
            ProfileField::PRESENTATION_TEXTAREA,
            ProfileField::PRESENTATION_PASSWORD,
            ProfileField::PRESENTATION_URL,
        ],
        "arrayValue" => [
            ProfileField::PRESENTATION_SINGLE_CHOICE,
            ProfileField::PRESENTATION_MULTI_CHOICE,
        ],
        "booleanValue" => [
            ProfileField::PRESENTATION_SWITCH
        ],
        "dateValue" => [
            ProfileField::PRESENTATION_DATE
        ]
    ];

    protected function isValueProperty($fieldName)
    {
        return array_key_exists($fieldName, $this->valueProps);
    }

    protected function getAllowedValueProperties($presentation)
    {
        $out = [];
        foreach ($this->valueProps as $name => $presentations) {
            if (in_array($presentation, $presentations)) {
                $out[] = $name;
            }
        }

        return $out;
    }
}
