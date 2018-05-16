<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

define('NXS_PLPATH', rtrim(APPPATH, '/\\' ). '/third_party/nextscripts/');  
define( 'NXS_PLURL','');  
define( 'NXS_SETV', 360);

require_once NXS_PLPATH."inc/nxs-networks-class.php";  
require_once NXS_PLPATH."nxs-snap-class.php"; 

 if (file_exists(NXS_PLPATH."nxs-user-functions.php")) 
     require_once NXS_PLPATH."nxs-user-functions.php"; 


class NextScriptsLib extends cl_nxsAutoPostToSN
{   
	function __construct($config = array())
	{	
        parent::__construct($config);
		log_message('debug', 'NextScriptsLib Class Initalized');
	}    
}

/* End of file ELFinderLib.php */
/* Location: ./application/libraries/ELFinderLib.php */