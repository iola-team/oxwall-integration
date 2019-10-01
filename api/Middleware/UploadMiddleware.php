<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Middleware;

use GraphQL\Error\InvariantViolation;
use GraphQL\Server\RequestError;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * This middleware responsibility is to handle `multipart/form-data` requests and convert them to `graphql` compatible form.
 * The code is copied from https://github.com/Ecodev/graphql-upload since we have to support php 5.6, at least for now.
 * TODO: Remove this copy-past when it will be possible
 *
 * @package Iola\Api\Middleware
 */
class UploadMiddleware
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        return $next(
            $this->processRequest($request),
            $response
        );
    }

    /**
     * Process the request and return either a modified request or the original one
     *
     * @param ServerRequestInterface $request
     *
     * @return ServerRequestInterface
     * @throws RequestError
     */
    public function processRequest(ServerRequestInterface $request)
    {
        $contentTypeHeaders = $request->getHeader('content-type');
        $contentType = empty($contentTypeHeaders[0])
            ? ''
            : $contentTypeHeaders[0];

        if (mb_stripos($contentType, 'multipart/form-data') !== false) {
            $this->validateParsedBody($request);
            $request = $this->parseUploadedFiles($request);
        }

        return $request;
    }
    /**
     * Inject uploaded files defined in the 'map' key into the 'variables' key
     *
     * @param ServerRequestInterface $request
     *
     * @return ServerRequestInterface
     * @throws RequestError
     */
    private function parseUploadedFiles(ServerRequestInterface $request)
    {
        $bodyParams = $request->getParsedBody();

        if (!isset($bodyParams['map'])) {
            throw new RequestError('The request must define a `map`');
        }

        $map = json_decode($bodyParams['map'], true);
        $result = json_decode($bodyParams['operations'], true);

        if (isset($result['operationName'])) {
            $result['operation'] = $result['operationName'];
            unset($result['operationName']);
        }

        foreach ($map as $fileKey => $locations) {
            foreach ($locations as $location) {
                $items = &$result;
                foreach (explode('.', $location) as $key) {
                    if (!isset($items[$key]) || !is_array($items[$key])) {
                        $items[$key] = [];
                    }
                    $items = &$items[$key];
                }
                $items = $request->getUploadedFiles()[$fileKey];
            }
        }

        return $request
            ->withHeader('content-type', 'application/json')
            ->withParsedBody($result);
    }
    /**
     * Validates that the request meet our expectations
     *
     * @param ServerRequestInterface $request
     * @throws InvariantViolation
     * @throws RequestError
     */
    private function validateParsedBody(ServerRequestInterface $request)
    {
        $bodyParams = $request->getParsedBody();

        if (null === $bodyParams) {
            throw new InvariantViolation(
                'PSR-7 request is expected to provide parsed body for "multipart/form-data" requests but got null'
            );
        }

        if (!is_array($bodyParams)) {
            throw new RequestError(
                'GraphQL Server expects JSON object or array, but got ' . Utils::printSafeJson($bodyParams)
            );
        }

        if (empty($bodyParams)) {
            throw new InvariantViolation(
                'PSR-7 request is expected to provide parsed body for "multipart/form-data" requests but got empty array'
            );
        }
    }
}
