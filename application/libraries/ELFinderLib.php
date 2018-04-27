<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ELFinderLib
{

    private  $opts = array();   
	function __construct($config = array())
	{	
        $this->opts = $config;
	}

    public function localConnector()
    {   
        $this->opts['roots'][0]['accessControl'] = array($this, 'elfinderAccess'); // disable and hide dot starting files (OPTIONAL)        
        $connectorObj = new elFinderConnector(new elFinder($this->opts));            
        return $connectorObj;
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

/* End of file ELFinderLib.php */
/* Location: ./application/libraries/ELFinderLib.php */