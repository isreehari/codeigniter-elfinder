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

//## Twitter API
require_once "../inc-cl/tw.api.php"; 

$message = array();
$message['text'] = 'Test Post';
$message['imageURL'] = 'http://www.nextscripts.com/imgs/nextscripts.png';

$TWoptions = array();

$TWoptions['twURL'] = 'https://twitter.com/YourTWPage';
$TWoptions['appKey'] = 'KEY';
$TWoptions['appSec'] = 'SEC';
$TWoptions['accessToken'] = 'TTT-TOK';
$TWoptions['accessTokenSec'] = 'TOKSEC';
$TWoptions['twMsgFormat'] = '%TEXT%';
$TWoptions['attchImg'] = '1';


$ntToPost = new nxs_class_SNAP_TW(); 
$ret = $ntToPost->doPostToNT($TWoptions, $message); 
prr($ret);
   
?>