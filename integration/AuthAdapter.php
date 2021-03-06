<?php

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
