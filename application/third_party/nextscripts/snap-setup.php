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

error_reporting(E_ALL);  

define('NXS_PLPATH', rtrim(dirname(__FILE__), '/\\' ). '/');  define( 'NXS_PLURL','');  define( 'NXS_SETV', 360);

require_once "inc/nxs-networks-class.php";  require_once "nxs-snap-class.php"; if (file_exists("nxs-user-functions.php")) require_once "nxs-user-functions.php"; 

$options = nxs_settings_open(); if (empty($options)) $options = array(); 

$needToSave = false; foreach ($nxs_snapAvNts as $avNt) {
  if (!empty($options[$avNt['lcode']])) { $uArr = array(); 
    foreach ($options[$avNt['lcode']] as $ii=>$aNt) if (isset($ii)&&$ii!=='') { $clName = 'nxs_snapClass'.$avNt['code'];  $ntt = new $clName; 
      if (method_exists($ntt,'toLatestVer')) $uArr[$ii] = $ntt->toLatestVer($aNt);  else $uArr[$ii] = $aNt;
      if (!empty($uArr[$ii]['isUpdd'])) { unset($uArr[$ii]['isUpdd']); $needToSave = true; }
    } $options[$avNt['lcode']] = $uArr; 
  }
} if ($needToSave) nxs_settings_save($options);

global $nxs_snapSetPgURL, $nxs_gOptions, $nxs_gNtwrks; $nxs_snapSetPgURL = nxs_currPageURL(); $nxs_gOptions = $options; $nxs_gNtwrks = $options; // prr($options);

$postTo = array('tw'=>array(0, 2), 'fb'=>array(0,1,2), 'li'=>array(0,1), 'gp'=>array(0,1,2)); 
//$options = nxs_filterOutSettings($postTo, $options);// prr($options);

//## AJAX 
if (!empty($_POST) && !empty($_POST['action']) && $_POST['action'] == 'nxs_snap_aj') {
  if ($_POST['nxsact']=='getNTset') { $ii = $_POST['ii']; $nt = $_POST['nt']; $ntl = strtolower($nt);  $clName = 'nxs_snapClass'.$nt; $ntObj = new $clName();  
	 if ($ii=='N') { if (!isset($options[$ntl]) || count($options[$ntl])==0) $mt=0; else $mt = 1+max(array_keys($options[$ntl])); $ntObj->showNewNTSettings($mt); } else { $pbo = $options[$ntl][$ii];  $pbo['ntInfo']['lcode'] = $ntl; $ntObj->showNTSettings($ii, $pbo, false); }
  }
  if ($_POST['nxsact']=='setNTset') { global $nxs_snapAvNts; unset($_POST['action']); unset($_POST['nxsact']); unset($_POST['_wp_http_referer']); unset($_POST['_wpnonce']); //unset($_POST['apDoSFB0']); // Do something
	if (get_magic_quotes_gpc() || (!empty($_POST['nxs_mqTest']) && $_POST['nxs_mqTest']=="\'")) {array_walk_recursive($_POST, 'nsx_stripSlashes');}  
	array_walk_recursive($_POST, 'nsx_fixSlashes');  unset($_POST['nxs_mqTest']); 
	foreach ($nxs_snapAvNts as $avNt) if (isset($_POST[$avNt['lcode']])) { $clName = 'nxs_snapClass'.$avNt['code']; if (!isset($options[$avNt['lcode']])) $options[$avNt['lcode']] = array(); 
	   $ntClInst = new $clName(); $ntOpt = $ntClInst->setNTSettings($_POST[$avNt['lcode']], $options[$avNt['lcode']]); $options[$avNt['lcode']] = $ntOpt;
	}  nxs_settings_save($options);  die('OK');
  } 
  if ($_POST['nxsact']=='getNewPostDlg') nxs_showNewPostForm($options);  
  if ($_POST['nxsact']=='tst') { echo "Testing [".$_POST['nt']."] ....<br/><br/>";
	$ntl = strtolower($_POST['nt']); $postTo = array($ntl=>array($_POST['nid'])); $options = nxs_filterOutSettings($postTo, $options);    
	$nxsAutoPostToSN = new cl_nxsAutoPostToSN($nxs_snapAPINts, $options);    
	$message = array(
	  'title'=>'Test. Please ignore', 'text'=>'Test Post. Please ignore', 'url'=>'http://www.nextscripts.com/', 
	  'urlDescr'=>'NextScripts: Social Networks Auto-Poster - Wordpress plugun and Universal API for all Social Networks', 
	  'urlTitle'=>'NextScripts: Social Networks Auto-Poster', 
	  'imageURL' => array('large'=>'http://www.nextscripts.com/wp-content/uploads/2013/11/SNAP-Logo2_big_SQ.png'),     
	  'siteName'=>'NextScritps' 
	);  
    
	//## Set Message
	$nxsAutoPostToSN->setMessage($message);
	//## Post Message
	$ret = $nxsAutoPostToSN->autoPost();  
	if (!empty($ret) && is_array($ret) && !empty($ret[$ntl][$_POST['nid']])){ 
	  if (!empty($ret[$ntl][$_POST['nid']]['isPosted'])) echo 'All OK. Post URL: <a target="_blank" href="'.$ret[$ntl][$_POST['nid']]['postURL'].'">'.$ret[$ntl][$_POST['nid']]['postURL'].'</a>';
	  if (!empty($ret[$ntl][$_POST['nid']]['Error'])) echo '<b style="color:red;">Error: </b>'.$ret[$ntl][$_POST['nid']]['Error'];
	} 
	echo "<br/><br/><hr/>Raw Output:<br/>";
	prr($ret); die();
  }
  if ($_POST['nxsact']=='doNewPost') nxs_doNewNPPost($options);  
  if ($_POST['nxsact']=='nsDN') { $indx = (int)$_POST['id'];  unset($options[$_POST['nt']][$indx]); nxs_settings_save($options); }  
  if ($_POST['nxsact']=='getItFromNT') { $ntU = strtoupper($_POST['nt']); $clName = 'nxs_snapClass'.$ntU; $ntObj = new $clName(); $fName = $_POST['fName']; $ntObj->$fName($options); die();}
  
  
  
  die();
}
//## END AJAX 

//## POST
if (!empty($_POST) && !empty($_POST['update_NS_SNAutoPoster_settings'])) {
  foreach ($nxs_snapAvNts as $avNt) if (isset($_POST[$avNt['lcode']])) { $clName = 'nxs_snapClass'.$avNt['code']; if (!isset($options[$avNt['lcode']])) $options[$avNt['lcode']] = array();
	  $ntClInst = new $clName(); $ntOpt = $ntClInst->setNTSettings($_POST[$avNt['lcode']], $options[$avNt['lcode']]); $options[$avNt['lcode']] = $ntOpt;
  } nxs_settings_save($options);
}
//## END POST

//prr($options);

if (class_exists("cl_nxsAutoPostToSN")) { $nxsAutoPostToSN = new cl_nxsAutoPostToSN($nxs_snapAPINts, $options);    
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"><html lang="en"><head> <meta http-equiv="content-type" content="text/html; charset=utf-8"><title>SNAP Setup</title>
<style type="text/css">body{font-family: "Open Sans",sans-serif; font-size: 12px;}ul{display: block;}</style><script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script><script type='text/javascript' src='js/js.js'></script>
<style type="text/css">
  .nxspButton:hover { background-color: #1E1E1E;}
  .nxspButton { background-color: #2B91AF; color: #FFFFFF; cursor: pointer; display: inline-block; text-align: center; text-decoration: none; border-radius: 6px 6px 6px 6px; box-shadow: none; font: bold 131% sans-serif; padding: 0 6px 2px; position: absolute; right: -7px; top: -7px;}
  #nxs_spPopup, #nxs_cntPopup, #nxs_popupDiv, #showLicForm{ min-height: 250px; z-index:999991; background-color: #FFFFFF; border-radius: 5px 5px 5px 5px;  box-shadow: 0 0 3px 2px #999999; color: #111111; display: none;  min-width: 850px; padding: 25px;}
  #nxsNewSNPost .nxsNPLabel {position: relative;}
  #nxsNewSNPost .nxsNPRow {position: relative; padding: 8px;}
  #nxsNewSNPost input {position: relative; font-size: 16px;}
  .nsx_iconedTitle {font-size: 17px; font-weight: bold; margin-bottom: 15px; padding-left: 20px; background-repeat: no-repeat; }
  .nxsNPRowSm, .nxsNPRow .nsx_iconedTitle {font-size: 12px; }
  .nxsNPLeft, .nxsNPRight {position: relative; float: left;}
  .nxsNPLeft {width: 40%;} .nxsNPRight {width: 60%;}
</style>
<style type="text/css">
.NXSButton { background-color:#89c403;
	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #89c403), color-stop(1, #77a809) );
	background:-moz-linear-gradient( center top, #89c403 5%, #77a809 100% );
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#89c403', endColorstr='#77a809');    
	-moz-border-radius:4px; -webkit-border-radius:4px; border-radius:4px; border:1px solid #74b807; display:inline-block; color:#ffffff;
	font-family:Trebuchet MS; font-size:12px; font-weight:bold; padding:4px 5px;  text-decoration:none;  text-shadow:1px 1px 0px #528009;
}.NXSButton:hover {color:#ffffff; background-color:#77a809;
	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #77a809), color-stop(1, #89c403) );
	background:-moz-linear-gradient( center top, #77a809 5%, #89c403 100% );
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#77a809', endColorstr='#89c403');    
}.NXSButton:active {color:#ffffff; position:relative; top:1px;}.NXSButton:focus {color:#ffffff; position:relative; top:1px;} .nsBigText{font-size: 14px; color: #585858; font-weight: bold; display: inline;}
#nxs_ntType {width: 150px;}
#nsx_addNT {width: 600px;}
.nxsInfoMsg{  margin: 1px auto; padding: 3px 10px 3px 5px; border: 1px solid #ffea90;  background-color: #fdfae4; display: inline; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; }
.blnkg{text-decoration:blink; font-size: 17px; color: #0CB107; font-weight: bold; display: inline;}

div.popShAtt { display: none; position: absolute; width: 600px; padding: 10px; background: #eeeeee; color: #000000; border: 1px solid #1a1a1a; font-size: 90%; }
.underdash {border-bottom: 1px #21759B dashed; text-decoration:none;}
.underdash a:hover {border-bottom: 1px #21759B dashed}

.nxsTHRow {vertical-align:top; padding-top:6px; text-align:right; width:80px; padding-right:10px;}

ul.nsx_tabs {margin: 0;padding: 0; margin-top:5px;float: left;list-style: none;height: 32px;border-bottom: 1px solid #999;border-left: 1px solid #999;width: 99%;}
ul.nsx_tabs li {float: left;margin: 0;padding: 0;height: 31px;line-height: 31px;border: 1px solid #999;border-left: none;margin-bottom: -1px;overflow: hidden;position: relative;background: #e0e0e0;}
ul.nsx_tabs li a {text-decoration: none;color: #000; display: block; font-size: 1.2em; padding: 0 20px; border: 1px solid #fff; outline: none;}
ul.nsx_tabs li a:hover { background: #ccc;}
html ul.nsx_tabs li.active, html ul.nsx_tabs li.active a:hover  { background: #fff; border-bottom: 1px solid #fff; }
.nsx_tab_container {border: 1px solid #999; border-top: none; overflow: hidden; clear: both; float: left; width: 99%; background: #fff;}
.nsx_tab_content {padding: 10px;}

.nxs_tls_cpt{width:100%; padding-bottom: 5px; padding-top: 10px;font-size: 16px; font-weight: bold;}
.nxs_tls_bd{width:100%; padding-left: 10px; padding-bottom: 10px;}
.nxs_tls_sbInfo{font-style: italic; padding-bottom: 10px; padding-top: 2px;}
.nxs_tls_sbInfo2{font-style: italic; padding-left: 10px; padding-bottom: 5px; line-height: 10px; font-size: 11px;}
.nxs_tls_lbl{width:100%;padding-top:7px;padding-bottom:1px;}
.nxsInstrSpan{ font-size: 11px; }


.subDiv{margin-left: 15px;}
.nxs_hili {color:#008000;}
.clNewNTSets{width: 800px;}
.nxclear {clear: both;}

.nxs_icon16 { font-size: 14px; line-height: 18px;
	background-position: 3px 50% !important;
	background-repeat: no-repeat !important;
	display: inline-block;
	padding: 1px 0 1px 23px !important;
}

.nxs_box{border-color: #DFDFDF; border-radius: 3px 3px 3px 3px; box-shadow: 0 1px 0 #FFFFFF inset; border-style: solid; border-width: 1px; line-height: 1; margin-bottom: 10px; padding: 0; max-width: 1080px;}
.nxs_box_header{border-bottom-color: #DFDFDF; box-shadow: 0 1px 0 #FFFFFF; text-shadow: 0 1px 0 #FFFFFF;font-size: 15px;font-weight: normal;line-height: 1;margin: 0;padding: 6px;
background:#f1f1f1;background-image:-webkit-gradient(linear,left bottom,left top,from(#ececec),to(#f9f9f9));background-image:-webkit-linear-gradient(bottom,#ececec,#f9f9f9);background-image:-moz-linear-gradient(bottom,#ececec,#f9f9f9);background-image:-o-linear-gradient(bottom,#ececec,#f9f9f9);background-image:linear-gradient(to top,#ececec,#f9f9f9)
-moz-user-select: none;border-bottom-style: solid;border-bottom-width: 1px;}
.nxs_box_inside{line-height: 1.4em; padding: 10px;}
.nxs_box_inside input[type=text]{ padding: 5px; height: 24px; border: 1px solid #ACACAC;}
.nxs_box_inside .insOneDiv, #nsx_addNT .insOneDiv{max-width: 1020px; background-color: #f8f9f9; background-repeat: no-repeat; margin: 10px; border: 1px solid #808080; padding: 10px; display:none;}
.nxs_box_inside .itemDiv {margin:5px;margin-left:10px;}
.nxs_box_header h3 {font-size: 14px; margin-bottom: 2px; margin-top: 2px;}
.nxs_newLabel {font-size: 11px; color:red; padding-left: 5px; padding-right: 5px;}

.nxs_prevImagesDiv {border:1px solid #0f3c6d;  width:110px; height:110px; margin:3px; padding:3px; text-align:center; float:left; position: relative;}
.nxs_prevImages {padding:1px; max-height:100px; max-width:100px;}
.nxs_chImg_selDiv {border:1px solid #800000;}
.nxs_chImg_selImg {border:4px solid #800000;}
.nxs_checkIcon{position: absolute;}

.nxs_checkIcon{display:none; height:24px;width:24px;position:absolute;top:-7px;right:-7px;outline:0;border:1px solid #fff;border-radius:3px;box-shadow:0 0 0 1px rgba(0,0,0,0.4);background:#800000;background-image:-webkit-gradient(linear,left top,left bottom,from(#800000),to(#570000));background-image:-webkit-linear-gradient(top,#800000,#570000);background-image:-moz-linear-gradient(top,#800000,#570000);background-image:-o-linear-gradient(top,#800000,#570000);background-image:linear-gradient(to bottom,#800000,#570000)}
.nxs_checkIcon{ top:-5px; right: -3px; width: 15px; height: 15px; box-shadow:0 0 0 1px #800000;background:#800000;background-image:-webkit-gradient(linear,left top,left bottom,from(#800000),to(#570000));background-image:-webkit-linear-gradient(top,#800000,#570000);background-image:-moz-linear-gradient(top,#800000,#570000);background-image:-o-linear-gradient(top,#800000,#570000);background-image:linear-gradient(to bottom,#800000,#570000)}
.nxs_checkIcon div{background-position:-21px 0; width: 15px; height: 15px;}

.doneMsg {color:#005800; font-weight: bold; display: inline-block; font-size: 16px; display: none; float: right; margin-right: 50px;}

.submit {padding-top: 10px;}
.button-primary.hover, .button-primary.focus { background: #1e8cbe; border-color: #0074a2; -webkit-box-shadow: inset 0 1px 0 rgba(120,200,230,.6); box-shadow: inset 0 1px 0 rgba(120,200,230,.6); color: #fff; }
.button-primary { background: #2ea2cc; border-color: #0074a2; -webkit-box-shadow: inset 0 1px 0 rgba(120,200,230,.5),0 1px 0 rgba(0,0,0,.15); box-shadow: inset 0 1px 0 rgba(120,200,230,.5),0 1px 0 rgba(0,0,0,.15); color: #fff;text-decoration: none;}
.button, .button-primary, .button-secondary { display: inline-block; text-decoration: none; font-size: 13px; line-height: 26px; height: 28px; margin: 0; padding: 0 10px 1px; cursor: pointer; border-width: 1px; border-style: solid;
  -webkit-border-radius: 3px; -webkit-appearance: none; border-radius: 3px; white-space: nowrap; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box;  

.nxsHiLightBorder {
  box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19); 
}

.nxs_ldos {color: #0073aa; cursor: pointer;}

.nxs_authPopup {display: block; position: absolute;top: 25px;left: 25px; width: 50%; height: 540px; max-width: 960px; padding: 0px; border: 6px solid #008000; background-color: white; z-index: 1002;}
.nxs_authPopupIn {z-index: 1003; height: 475px; overflow: auto; padding: 10px;}
.nxs_authPopupHdr { background-color: #008000; color: white; height: 24px; padding: 10px;}
.nxs_authPopupClose { background-color: white; color: #008000; height: 24px; float: right; padding: 4px;}

.nxsTeal {color:#008080}
.nxsBlue {color:#025294}
.nxsGreen {color:#005800}
.nxsOrange {color:#FF8800}
.nxsDarkRed {color:#800000}
.nxsDarkOrange {color:#884900}

pre { white-space: pre-wrap; white-space: -moz-pre-wrap; white-space: -pre-wrap; white-space: -o-pre-wrap; word-wrap: break-word; }
</style>
<script type="text/javascript"><?php echo "var ajaxurl = '".$nxs_snapSetPgURL."';"; ?></script>
</head><body><form method="post">
<a href="#" class="NXSButton" id="nxs_snapAddNew">Add new account</a> <div class="nxsInfoMsg"><img style="position: relative; top: 8px;" alt="Arrow" src="img/arrow_l_green_c1.png"/> You can add Facebook, Twitter, Google+, Pinterest, LinkedIn, Tumblr, Blogger/Blogspot, Delicious, etc accounts</div><br/><br/>
		  <div id="nxs_spPopup"><span class="nxspButton bClose"><span>X</span></span>Add New Network: <select onchange="doGetHideNTBlock(this.value, 'N');" id="nxs_ntType"><option value =""></option>
			 <?php foreach ($nxs_snapAvNts as $avNt) echo '<option value ="'.$avNt['code'].'">'.$avNt['name'].'</option>'; ?>
		   </select>           
		   <div id="nsx_addNT">
			 <?php // foreach ($nxs_snapAvNts as $avNt) { $clName = 'nxs_snapClass'.$avNt['code']; $ntClInst = new $clName(); if (!isset($options[$avNt['lcode']]) || count($options[$avNt['lcode']])==0) { $ntClInst->showNewNTSettings(0); } } ?>      
		   </div>
		   
		   </div>
		   <div id="nxs_cntPopup"><span class="nxspButton bClose"><span>X</span></span> 
		   <div class="nxs_pppSpinner" style="text-align: center;"><img src="http://gtln.us/img/misc/ajax-loader-med.gif"/></div>
		   <div style="overflow: auto; border: 1px solid #999; max-width: 720px; max-height: 400px; font-size: 11px;">             
			 <div class="nxsAJcnt"></div>
		   </div>
		   
		   </div>
		   <div id="nxsAllAccntsDiv"><div class="nxs_modal"></div>
<br/><?php
  $nxsAutoPostToSN->showAllSettings();  
?></div></form></body></html><?php  
}
?>