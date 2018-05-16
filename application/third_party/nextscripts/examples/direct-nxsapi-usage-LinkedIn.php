<?php

/*#############################################################################
Project Name: NextScripts Social Networks AutoPoster
Project URL: http://www.nextscripts.com/snap-api/
Description: Automatically posts to all your Social Networks
Author: NextScripts, Inc
Version: Beta 27 (Dec 7, 2016)
Author URL: http://www.nextscripts.com
Copyright 2012-2016  NextScripts, Inc
#############################################################################*/

require_once "../nxs-api/nxs-api.php";  
require_once "../nxs-api/nxs-http.php"; 
require_once "../inc/nxs-functions.php";

  $email = 'YourEmail@gmail.com'; 
  $pass = 'YourPassword';
  $msg = 'Post this to LinkedIn Company Page'; 
  $to = 'https://www.linkedin.com/company/MySuperCompany';
  $lnkArr['postType'] = 'A'; 
  $lnkArr['url'] = 'http://www.nextscripts.com/';
  $nt = new nxsAPI_LI();
  $loginError = $nt->connect($email, $pass);     
  if (!$loginError)
    {
      $result = $nt -> post($msg);
    } 
  else echo $loginError; 
  
  if (!empty($result) && is_array($result) && !empty($result['post_url'])) 
    echo '<a target="_blank" href="'.$result['post_url'].'">New Post</a>'; 
  else 
    echo "<pre>".print_r($result, true)."</pre>";
?>