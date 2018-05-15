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
     * @param mixed $value
     *
     * @return mixed|void
     */
    public function serialize($value)
    {
        $dateTime = new DateTime();
        $dateTime->setTimestamp($value);

        return (string) $dateTime->format($this->format);
    }

    /**
     * @param mixed $value
     *
     * @return mixed|void
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

        return $dateTime->getTimestamp();
    }

    /**
     * @param \GraphQL\Language\AST\Node $ast
     *
     * @return mixed|void
     */
    public function parseLiteral($ast)
    {
        if ($ast instanceof StringValueNode) {
            $dateTime = new DateTime($ast->value);

            return $dateTime ? $dateTime->getTimestamp() : null;
        }

        return null;
    }
}
