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

//error_reporting(E_ALL);

require_once "../nxs-snap-class.php"; // require_once "nxs-user-functions.php"; 

//## Get configured networks settings from file/DB/yourPlace. ### Change this in the nxs-user-functions.php file and include it. Please see sample--nxs-user-functions.php file
$options = nxs_settings_open(); 

//## Use all Networks
// $postOptions = $dbOptions;

//## Use only first Facebook account and first and third Twitter accounts
$postTo = array('tw'=>array(0, 2), 'fb'=>array(0), 'gp'=>array(0)); 
$options = nxs_filterOutSettings($postTo, $options);

//## Initialize class and prepage the message
if (class_exists("cl_nxsAutoPostToSN")) { $nxsAutoPostToSN = new cl_nxsAutoPostToSN($nxs_snapAPINts, $options);    
  $message = array(
    'title'=>'Toshiba details 4K laptop arriving before summer', 
    'announce'=>'', 
    'text'=>'Toshiba has just dished most of the important details about its intriguing 4K Satellite P50t that we saw at CES 2014, along with some other new models.  There will be  that exotic 282 ppi 3,840 x 2,160, 15.6-inch touchscreen, driven by 2GB AMD Radeon R9 M265 graphics', 
    'url'=>'http://www.engadget.com/2014/03/19/toshiba-details-4k-laptop-arriving-before-summer/', 
    'surl'=>'', 
    'urlDescr'=>'Toshiba has just dished most of the important details about its intriguing 4K Satellite P50t that we saw at CES 2014', 
    'urlTitle'=>'Toshiba details 4K laptop arriving before summer', 
    'imageURL' => array('large'=>'http://o.aolcdn.com/hss/storage/adam/f6428d1133fd5a94e05dd17b7cd80e0/Satellite-p50t-b-2013-03-19-01.jpg'), 
    'videoCode'=>'', 
    'videoURL'=>'', 
    'siteName'=>'Engadget', 
    'tags'=>'Toshiba, 4K', 
    'cats'=>'electronics', 
    'authorName'=>'', 
    'orID'=>''
    );
  
  //## Set Message
  $nxsAutoPostToSN->setMessage($message);
  //## Post Message
  $ret = $nxsAutoPostToSN->autoPost(); 
  
  echo "## Return";
  prr($ret);

}