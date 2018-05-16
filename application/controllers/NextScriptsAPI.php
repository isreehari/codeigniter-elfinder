<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class NextScriptsAPI extends CI_Controller {

    public function __construct(){
		parent::__construct();		
		$this->load->library('NextScriptsLib','nextscriptsLib');
    }
	
	public function index(){		    			
            $this->load->view('nextscripts');
	}   
    
}
