


<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @author isreehari
 * @email isreehari@hotmail.com
 * @create date 2018-05-15 04:28:22
 * @modify date 2018-05-15 04:28:22
 * @desc [description]
*/

class ELFinderLib
{

    private  $opts = array();   
	function __construct($config = array())
	{	
        $this->opts = $config;
	}

    public function localConnector()
    {   
        $this->opts['bind'] = array(
                                        'upload.presave' => array($this,'preUpload'),
                                    );
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


    public function preUpload(&$path, &$name, $src, $elfinder, $volume){            

            $currentYear = date("Y");
            $currentMonth = date("m");
            
            $realpath = $elfinder->realpath($path);
            $realpath =  $realpath. '/' .$currentYear;

            if(!file_exists($realpath) || !is_dir($realpath)){
                $args = array('target' => $path, 'name' => $currentYear, 'reqid' => '' );
                $result = $elfinder->exec('mkdir', $args);
                if($result && $result["added"]){
                    $path = $result['added'][0]['hash'];         

                    $realpath = $realpath . '/' .  $currentMonth;
                    $args = array('target' => $path, 'name' => $currentMonth, 'reqid' => '' );
                    $result = $elfinder->exec('mkdir', $args);
                    if($result && $result["added"]){
                        $path = $result['added'][0]['hash'];    
                    }else{
                        return false;
                    }
                }else{
                    return false;
                }              
            }else{
                $realpath = $realpath . '/' .  $currentMonth;
                if(!file_exists($realpath) || !is_dir($realpath)){
                    $args = array('target' => $path, 'name' => $currentMonth, 'reqid' => '' );
                    $result = $elfinder->exec('mkdir', $args);
                    if($result && $result["added"]){
                        $path = $result['added'][0]['hash'];    
                    }else{
                        $path = $volume->getHash($path, $currentMonth);
                        return true;
                    }
                }                

            }

            $path = $volume->getHash($realpath, '');

            return false;
    }   
        
}




/* End of file ELFinderLib.php */
/* Location: ./application/libraries/ELFinderLib.php */