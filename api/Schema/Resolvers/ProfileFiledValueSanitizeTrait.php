<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Entities\ProfileField;
use Everywhere\Api\Entities\ProfileFieldValue;
use alroniks\dtms\DateTime;
use GraphQL\Error\InvariantViolation;
use GraphQL\Utils\Utils;

/**
 * Super dirty solution for now
 * We will definitely rethink it in the future
 *
 * TODO: RE-FACTOR THE LOGIC ASAP TO USE GRAPHQL TYPE SYSTEM OR MORE ELEGANT VALIDATION APPROACH
 *
 * @package Everywhere\Api\Schema\Resolvers
 */
trait ProfileFiledValueSanitizeTrait
{
    protected function sanitizeOutputValue(ProfileField $field, $value)
    {
        return $this->sanitize($field, $value, "output");
    }

    protected function sanitizeInputValue(ProfileField $field, $value)
    {
        return $this->sanitize($field, $value, "input");
    }

    private function sanitize(ProfileField $field, $value, $direction)
    {
        if ($value === null) {
            return $value;
        }

        switch ($field->presentation) {
            case ProfileField::PRESENTATION_SINGLE_CHOICE:
            case ProfileField::PRESENTATION_MULTI_CHOICE:
                if (!is_array($value)) {
                    throw new InvariantViolation(
                        "The field value should be array of strings but received: " . Utils::printSafe($value)
                    );
                }

                $value = array_map("strval", $value);

                break;

            case ProfileField::PRESENTATION_TEXT:
            case ProfileField::PRESENTATION_TEXTAREA:
            case ProfileField::PRESENTATION_PASSWORD:
            case ProfileField::PRESENTATION_URL:
                $value = (string) $value;
                break;

            case ProfileField::PRESENTATION_SWITCH:
                $value = (boolean) $value;
                break;

            case ProfileField::PRESENTATION_DATE:
                if ($direction === "input") {
                    $value = DateTime::createFromFormat(DateTime::ISO8601, $value);
                } else {
                    if (is_int($value)) {
                        $value = (new DateTime())->setTimestamp($value);
                    }

                    if (!$value instanceof \DateTime) {
                        throw new InvariantViolation(
                            "The field ({$field->getId()}) value should be an instance of `DateTime` but received: " . Utils::printSafe($value)
                        );
                    }

                    /**
                     * @var $value \DateTime
                     */
                    $value = $value->format(DateTime::ISO8601);
                }

                break;
        }

        return $value;
    }
}
