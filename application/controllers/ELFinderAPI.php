<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ELFinderAPI extends CI_Controller {

    public function __construct(){
		parent::__construct();		
		$this->load->library('ELFinderLib','elfinderlib');
    }
	
	public function index(){		    
			$data['connector'] = 'http://codeigniter.elfinder/elfinderapi/connector';			
            $this->load->view('elfinder', $data);
	}   
        
    public function connector(){
        $this->elfinderlib->localConnector()->run();
    }
        
    
}
