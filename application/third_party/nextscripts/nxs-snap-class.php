<?php
/*#############################################################################
Project Name: NextScripts Social Networks AutoPoster
Project URL: http://www.nextscripts.com/snap-api/
Description: Automatically posts to all your Social Networks
Author: NextScripts, Inc
Version: 4.2.1 (Feb, 17, 2018)
Author URL: http://www.nextscripts.com
Copyright 2012-2018  NextScripts, Inc
#############################################################################*/

// base directory for the namespace prefix.
$base_dir = __DIR__;   // By default, it points to this same folder.
// You may change this path if having trouble detecting the path to
// the source files.



require_once $base_dir."/inc/nxs-functions.php"; 
require_once $base_dir."/inc/nxs_oauth_class.php"; 
require_once $base_dir."/nxs-api/nxs-api.php";  
require_once $base_dir."/nxs-api/nxs-http.php";

$nxs_snapAPINts = array(); 

foreach (glob(dirname( __FILE__ ).'/inc-cl/*.php') as $filename){	
	   require_once $filename; 
	} 

if (!class_exists("cl_nxsAutoPostToSN")) {
	class cl_nxsAutoPostToSN {//## General Functions         
		var $options = array(); 
		var $networks = array();

        protected $message = array(
		  'message' => '',
		  'link' => '',
		  'title' => '',
		  'description' => '',          
		  'imageURL' => '',
		  'videoURL' => '',
		  'tags' => '',
		  'categories' => '',
		  'siteName' => '',
		  'options' => array()
		); 
		
		//## Constructor
		function __construct($networks, $optionsData) { $this->networks = $networks;  $this->options = $this->setOptions($optionsData); }         
		function nxsAutoPostToSN() { }
		function setOption($nt, $num, $ntName, $value) { 
			$opts = $this->options;  if (!is_array($opts)) $opts = array(); if (!is_array($opts[$nt])) $opts[$nt] = array(); if (!is_array($opts[$nt][$num])) $opts[$nt][$num] = array(); 
			$opts[$nt][$num][$ntName] = $value; $this->options = $opts;
		}
		function setOptions($optionsData) {  $options = array();          
			$dbOptions = maybe_unserialize($optionsData); if(!is_array($dbOptions)) die('No Settings found');
			if (!empty($dbOptions) && is_array($dbOptions)) foreach ($dbOptions as $key => $option) if (trim($key)!='') $options[$key] = $option;             
			return $options;
		}
		function setMessage($message) { if (is_array($message)) $this->message = array_merge($this->message, $message); else $this->message['message'] = $message; }
		function autoPost() { 
			$options = $this->options;  $networks = $this->networks; $out = array(); 
			foreach ($networks as $nt) if (isset($nt['lcode'])) { $clName = 'nxs_class_SNAP_'.$nt['code']; if (!isset($options[$nt['lcode']])) continue; 
			  $ntClInst = new $clName(); $out[$nt['lcode']] = $ntClInst->doPost($options[$nt['lcode']], $this->message); 
			} 
			return $out;
		}
		
		function showAllSettings() { 
			$options = $this->options;  $networks = $this->networks; $out = array(); 
			foreach ($networks as $nt) if (isset($nt['lcode'])) { $clName = 'nxs_snapClass'.$nt['code']; if (!isset($options[$nt['lcode']])) continue; 
			  $ntClInst = new $clName(); $ntClInst->showGenNTSettings($options[$nt['lcode']]); 
			}             
		}
	}
}
?>