<?php

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Contract\Schema\ScalarTypeResolverInterface;
use GraphQL\Error\Error;
use GraphQL\Error\InvariantViolation;
use Psr\Http\Message\UploadedFileInterface;

class UploadResolver implements ScalarTypeResolverInterface
{
    /**
     * @param mixed $value
     * @return mixed|void
     * @throws InvariantViolation
     */
    public function serialize($value)
    {
        throw new InvariantViolation('`Upload` cannot be serialized');
    }

    /**
     * @param mixed $value
     * @return mixed
     * @throws \UnexpectedValueException
     */
    public function parseValue($value)
    {
        if (!$value instanceof UploadedFileInterface) {
            throw new \UnexpectedValueException(
                'Could not get uploaded file, be sure to conform to GraphQL multipart request specification. Instead got: ' . Utils::printSafe($value)
            );
        }

        return $value;
    }

    /**
     * @param \GraphQL\Language\AST\Node $valueNode
     * @return mixed|void
     * @throws Error
     */
    public function parseLiteral($valueNode)
    {
        throw new Error(
            '`Upload` cannot be hardcoded in query, be sure to conform to GraphQL multipart request specification. Instead got: ' . $valueNode->kind, [$valueNode]
        );
    }
}
