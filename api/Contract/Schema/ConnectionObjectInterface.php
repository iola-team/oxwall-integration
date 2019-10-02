<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Contract\Schema;

use GraphQL\Executor\Promise\Promise;

interface ConnectionObjectInterface
{
    /**
     * @return mixed
     */
    public function getRoot();

    /**
     * @param mixed|null $arguments
     * @return Promise
     */
    public function getItems($arguments = null);

    /**
     * @param mixed|null $arguments
     * @return Promise
     */
    public function getCount($arguments = null);

    /**
     * @return array
     */
    public function getArguments();
}
