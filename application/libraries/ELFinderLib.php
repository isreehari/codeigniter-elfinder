


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


    public function preUpload(&$path, &$name, $src, $elfinder, $volume)
    {
            $ext = '';
            if ($pos = strrpos($name, '.')) {
                $ext = substr($name, $pos);
            }

            // log_message('error', print_r($path,true)); // l1_XA
            // log_message('error', print_r($name,true)); //7a47bf5f-2115-4750-96eb-6dc2d2193bac.jpg
            // log_message('error', print_r($src,true)); //C:\wamp64\tmp\php104C.tmp
            // log_message('error', print_r($elfinder,true)); //Object
            //log_message('error', print_r($volume->ARGS->,true));
           
            // cmd: mkdir
            // name: hello
            // target: l1_XA
            // reqid: 1636a3459c56a
            
            $realpath = $elfinder->realpath($path);
            $realpath =  $realpath. '/'.'hari';

            if(!file_exists($realpath) || !is_dir($realpath)){
                $args = array('target' => $path, 'name' => 'hari', 'reqid' => '' );
                $result = $elfinder->exec('mkdir', $args);
                if($result && $result["added"]){
                    $path = $result['added'][0]['hash'];

                }else{
                    return false;
                }                
            }

             


            

            //$name =  $name . $ext; // With your preferred hashing method

            return true;
    }   
        
}




/* End of file ELFinderLib.php */
/* Location: ./application/libraries/ELFinderLib.php */