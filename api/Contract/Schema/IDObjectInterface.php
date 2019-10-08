<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Contract\Schema;
use JsonSerializable;

interface IDObjectInterface extends JsonSerializable
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getGlobalId();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function __toString();
}
