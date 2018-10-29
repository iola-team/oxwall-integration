<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Schema\ScalarTypeResolverInterface;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Utils\Utils;
use alroniks\dtms\DateTime;

class DateResolver implements ScalarTypeResolverInterface
{
    protected $format = DateTime::ISO8601;

    /**
     * @param \DateTime $value
     *
     * @return string
     */
    public function serialize($value)
    {
        if (is_int($value)) {
            $value = (new DateTime())->setTimestamp($value);
        }

        if (!$value instanceof \DateTime) {
            throw new InvariantViolation(
                "Date type can only represent `DateTime` instance or unix timestamp value, but received: "
                . Utils::printSafe($value)
            );
        }

        return $value->format($this->format);
    }

    /**
     * @param mixed $value
     *
     * @return \DateTime
     */
    public function parseValue($value)
    {
        $dateTime = DateTime::createFromFormat($this->format, $value);

        if (!$dateTime) {
            throw new InvariantViolation(
                "Date type should be of the following format: {$this->format} 
                but received this value: " . Utils::printSafe($value)
            );
        }

        return $dateTime;
    }

    /**
     * @param \GraphQL\Language\AST\Node $ast
     *
     * @return \DateTime|null
     */
    public function parseLiteral($ast)
    {
        if ($ast instanceof StringValueNode) {
            $dateTime = DateTime::createFromFormat($this->format, $ast->value);

            return $dateTime ? $dateTime : null;
        }

        return null;
    }
}
