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

//## More examples here: http://www.nextscripts.com/google-plus-automated-posting/

require_once "../nxs-api/nxs-api.php";  
require_once "../nxs-api/nxs-http.php"; 
require_once "../inc/nxs-functions.php";

  $email = 'YourEmail@gmail.com'; 
  $pass = 'YourPassword';
  $msg = 'Post this to Google Plus!'; 
  $pageID = '109888164682746252347';
  $lnk = 'http://www.nextscripts.com/social-networks-auto-poster-for-wp-multiple-accounts';

  // #############################################################################  
  //## Post simple message to account profile. 
   
  $nt = new nxsAPI_GP();
  $loginError = $nt->connect($email, $pass);     
  if (!$loginError)
  {
	  $result = $nt -> postGP($msg);
  } 
  else echo $loginError; 
  
  if (!empty($result) && is_array($result) && !empty($result['postURL'])) 
    echo '<a target="_blank" href="'.$result['postURL'].'">New Post</a>'; else echo "<pre>".print_r($result, true)."</pre>";
	
  // #############################################################################  
  //## Post simple message to page. 
  
  $nt = new nxsAPI_GP();
  $loginError = $nt->connect($email, $pass);     
  if (!$loginError)
	{
	  $result = $nt -> postGP($msg, '', $pageID);
	} 
  else echo $loginError; 
  
  if (!empty($result) && is_array($result) && !empty($result['postURL'])) 
	echo '<a target="_blank" href="'.$result['postURL'].'">New Post</a>'; else echo "<pre>".print_r($result, true)."</pre>";

  // #############################################################################
  //## Post link message to page.      
  $nt = new nxsAPI_GP();
  $loginError = $nt->connect($email, $pass);     
  if (!$loginError)
	{
	  $lnk = array('img'=>'http://www.nextscripts.com/imgs/nextscripts.png'); 
	  $result = $nt -> postGP($msg, $lnk, $pageID);
	  
	  if (!empty($result) && is_array($result) && !empty($result['postURL'])) 
		echo '<a target="_blank" href="'.$result['postURL'].'">New Post Link</a>'; else echo "<pre>".print_r($result, true)."</pre>";
	} 
  else echo $loginError; 
  
  // #############################################################################
  //## Post link message to Community.      
  
  $commID = '106583748714548203919'; 
  $commCategoryID = 'e8d33d89-4d26-43dd-a367-2894d6a55a28'; 
    
  $nt = new nxsAPI_GP();
  $loginError = $nt->connect($email, $pass);     
  if (!$loginError)
    {
      $result = $nt -> postGP($msg, $lnk, '', $commID, $commCategoryID);
    } 
  else echo $loginError; 
  
  if (!empty($result) && is_array($result) && !empty($result['postURL'])) 
    echo '<a target="_blank" href="'.$result['postURL'].'">New Post</a>'; 
  else 
    echo "<pre>".print_r($result, true)."</pre>"; 

	
?>