<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Contract\Schema\Relay;

interface EdgeFactoryInterface
{
    /**
     * Creates an edge object based on provided connection item and arguments
     * Creates default cursor if item was not provided
     *
     * @param array $filter
     * @param mixed|null $rootValue
     *
     * @return EdgeObjectInterface
     */
    public function create($filter, $rootValue = null);

    /**
     * Creates an edge object for a connection item after a cursor
     *
     * @param array $cursor
     * @param mixed $rootValue
     *
     * @return EdgeObjectInterface
     */
    public function createBefore($cursor, $rootValue);

    /**
     * Creates an edge object for a connection item before a cursor
     *
     * @param array $cursor
     * @param mixed $rootValue
     * @return EdgeObjectInterface
     */
    public function createAfter($cursor, $rootValue);

    /**
     * @param $arguments
     * @param $rootValue
     *
     * @return EdgeObjectInterface
     */
    public function createFromArguments($arguments, $rootValue);
}
