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

  $login = 'YourInstagramUserName'; 
  $pass = 'YourInstagramPassword';
  $msg = 'Post this to Instagram! #testpost'; 
  $imgURL = 'http://www.YourWebsiteURL.com/link/to/your/image.jpg'; 
  $imgFormat = 'E'; // 'E' (Extended) or 'C' (Cropped) or 'U' (Untouched)
  $nt = new nxsAPI_IG();
  $loginError = $nt->connect($email, $pass);     
  if (!$loginError)
    {
      $result = $nt -> post($msg, $imgURL, $imgFormat);
    } 
  else echo $loginError; 
  
  if (!empty($result) && is_array($result) && !empty($result['post_url'])) 
    echo '<a target="_blank" href="'.$result['post_url'].'">New Post</a>'; 
  else 
    echo "<pre>".print_r($result, true)."</pre>";
?>