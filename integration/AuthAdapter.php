<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Oxwall;

use OW_AuthResult;
use OW_AuthAdapter;

/**
 * Oxwall auth adapter wich always return failure result.
 * It need to hack Oxwall hardcoded authentication process
 * 
 * TODO: remove it when possible
 */
class AuthAdapter extends OW_AuthAdapter
{
   public function authenticate()
   {
       return new OW_AuthResult(OW_AuthResult::FAILURE);
   }
}
