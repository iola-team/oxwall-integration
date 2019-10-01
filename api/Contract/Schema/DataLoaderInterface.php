<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Contract\Schema;

use GraphQL\Executor\Promise\Promise;

interface DataLoaderInterface
{
    /**
     * @param $key
     * @param array $args
     *
     * @return Promise
     */
    public function load($key, array $args = []);

    /**
     * @param $keys
     * @param array $args
     *
     * @return Promise
     */
    public function loadMany($keys, array $args = []);

    /**
     * @param $key
     * @param array $args
     *
     * @return DataLoaderInterface
     */
    public function clear($key, array $args = []);

    /**
     * @return DataLoaderInterface
     */
    public function clearAll();

    /**
     * @param $key
     * @param $value
     * @param array $args
     *
     * @return DataLoaderInterface
     */
    public function prime($key, $value, array $args = []);
}
