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

require_once "../nxs-api/nxs-http.php"; 
require_once "../inc/nxs-functions.php";

//## Facebook API
require_once "../inc-cl/fb.api.php"; 

  $message = array();
  $message['text'] = 'Test Post';
  $message['imageURL'] = 'http://www.nextscripts.com/imgs/nextscripts.png';
  $message['url'] = 'http://www.nextscripts.com/snap-features/assign-categories-to-each-social-network/';
  $NToptions = array();
  $NToptions['fbURL'] = 'https://www.facebook.com/MYFBPAGE';
  $NToptions['appKey'] = 'APPID';
  $NToptions['appSec'] = 'SEC';
  $NToptions['accessToken'] = 'user_token';
  $NToptions['pageAccessToken'] = 'page_token'; //## If you are posting to a page
  $NToptions['postType'] = 'A';
  $ntToPost = new nxs_class_SNAP_FB(); 
  $result = $ntToPost->doPostToNT($NToptions, $message);   
  
  if (!empty($result) && is_array($result) && !empty($result['postURL'])) 
    echo '<a target="_blank" href="'.$result['postURL'].'">New Post</a>'; 
  else 
    echo "<pre>".print_r($result, true)."</pre>";
?>