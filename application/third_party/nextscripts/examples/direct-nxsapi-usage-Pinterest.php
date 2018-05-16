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

// Also please see here: http://www.nextscripts.com/pinterest-automated-posting/bot-protection/

require_once "../nxs-api/nxs-api.php";  
require_once "../nxs-api/nxs-http.php"; 
require_once "../inc/nxs-functions.php";
  
  $email = 'YourEmail@gmail.com'; 
  $pass = 'YourPassword';
  $msg = 'Pin this to Pinterest!';   
  $imgURL = 'http://www.YourWebsiteURL.com/link/to/your/image.jpg'; //## Must be real Image URL or you will get "Error 500"  
  $link = 'http://www.YourWebsiteURL.com/page'; //## Must be real URL or you will get "Error 500"
  $boardID = '158540918071326922'; 
  
  $nt = new nxsAPI_PN();
  
  $loginError = $nt->connect($email, $pass);     
  if (!$loginError)
    {
      $result = $nt -> post($msg, $imgURL, $link, $boardID);
    } 
  else echo $loginError; 
  
  if (!empty($result) && is_array($result) && !empty($result['post_url'])) 
    echo '<a target="_blank" href="'.$result['post_url'].'">New Post</a>'; 
  else 
    echo "<pre>".print_r($result, true)."</pre>";
?>