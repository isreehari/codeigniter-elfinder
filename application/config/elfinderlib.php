<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config = array(
                'roots' => array(
                    array( 
                        'driver'        => 'LocalFileSystem',
                        'path'          =>  APPPATH.'/files',
                        'URL'           => "",
                        'uploadDeny'    => array('all'),                  // All Mimetypes not allowed to upload
                        'uploadAllow'   => array('image', 'text/plain', 'application/pdf'),// Mimetype `image` and `text/plain` allowed to upload
                        'uploadOrder'   => array('deny', 'allow'),        // allowed Mimetype `image` and `text/plain` only                       
                        // more elFinder options here
                    ) 
                ),                
            );