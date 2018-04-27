<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ELFinder extends CI_Controller {

    public function __construct(){
		parent::__construct();		
		$this->load->library('ELFinderLib','elfinderlib');
    }
	
	public function index()
	{
		$this->load->view('elfinder');
	}

    public function connector()
	{
		$this->elfinderlib->connector()->run();
	}
}
