<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ELFinderLib 
{

    private  $elfinder_config = array();
    private  $ci = null;
	function __construct($config = array())
	{	        
           
        $this->ci =& get_instance();
        $this->ci->load->helper('url_helper');
        //$config['base_url'] = site_url((config_item('index_page') == '' ? SELF : '').$config['base_url']);
        $this->elfinder_config = $config;
             		
	}

        public function connector()
        {
            
            $opts = array(
                'roots' => array(
                    array( 
                        'driver'        => 'LocalFileSystem',
                        'path'          => FCPATH . '/files',
                        'URL'           => base_url('files'),
                        'uploadDeny'    => array('all'),                  // All Mimetypes not allowed to upload
                        'uploadAllow'   => array('image', 'text/plain', 'application/pdf'),// Mimetype `image` and `text/plain` allowed to upload
                        'uploadOrder'   => array('deny', 'allow'),        // allowed Mimetype `image` and `text/plain` only
                        'accessControl' => array($this, 'elfinderAccess'),// disable and hide dot starting files (OPTIONAL)
                        // more elFinder options here
                    ) 
                ),
            );
            $connector = new elFinderConnector(new elFinder($opts));
            return $connector;
        }
        
        public function elfinderAccess($attr, $path, $data, $volume, $isDir, $relpath)
        {
            $basename = basename($path);
            return $basename[0] === '.'                  // if file/folder begins with '.' (dot)
                     && strlen($relpath) !== 1           // but with out volume root
                ? !($attr == 'read' || $attr == 'write') // set read+write to false, other (locked+hidden) set to true
                :  null;                                 // else elFinder decide it itself
        }

	


        

        
        
}

/* End of file RichFilemanagerLib.php */
/* Location: ./application/libraries/RichFilemanagerLib.php */