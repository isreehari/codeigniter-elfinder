<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*!
* HybridAuth
* http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
* (c) 2009-2012, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html
*/

// ----------------------------------------------------------------------------------------
//	HybridAuth Config file: http://hybridauth.sourceforge.net/userguide/Configuration.html
// ----------------------------------------------------------------------------------------


switch (ENVIRONMENT) {
  case 'development': $config ='';  break;
	case 'testing':
	case 'production': $config ='';   break;
}





/* End of file hybridauthlib.php */
/* Location: ./application/config/hybridauthlib.php */
