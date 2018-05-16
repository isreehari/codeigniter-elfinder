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

require_once "../nxs-snap-class.php";
$nxs_plurl = '../';


//## Get configured networks settings from file/DB/yourPlace. ### Change this in the nxs-user-functions.php file and include it. Please see sample--nxs-user-functions.php file
$options = nxs_settings_open();

  if (isset($_POST['action']) && $_POST['action']=='nxs_snap_aj')  {
      if (!empty($_POST['mNts']) && is_array($_POST['mNts'])) { 
      $message = array('title'=>'', 'text'=>'', 'siteName'=>'', 'url'=>'', 'imageURL'=>'', 'videoURL'=>'', 'tags'=>'', 'urlDescr'=>'', 'urlTitle'=>'');  
      if (get_magic_quotes_gpc() || $_POST['nxs_mqTest']=="\'") { $_POST['mText'] = stripslashes($_POST['mText']); $_POST['mTitle'] = stripslashes($_POST['mTitle']); }
      $message['pText'] = $_POST['mText'];   $message['pTitle'] = $_POST['mTitle'];
      //## Get URL info
      if (!empty($_POST['mLink']) && substr($_POST['mLink'], 0, 4)=='http') { $message['url'] = $_POST['mLink'];            
        $flds = array('id'=>$message['url'], 'scrape'=>'true');      $response =  wp_remote_post('http://graph.facebook.com', array('body' => $flds)); 
        if (!is_nxs_error($response)) { $response = json_decode($response['body'], true);  
          if (!empty($response['description'])) $message['urlDescr'] = $response['description'];  if (!empty($response['title'])) $message['urlTitle'] =  $response['title'];
          if (!empty($response['site_name'])) $message['siteName'] = $response['site_name'];
          if (!empty($response['image'][0]['url'])) $message['imageURL'] = $response['image'][0]['url'];
        }
      }
      if (!empty($_POST['mImg']) && substr($_POST['mImg'], 0, 4)=='http') $message['imageURL'] = $_POST['mImg']; 
          
      foreach ($_POST['mNts'] as $ntC){ $ntA = explode('--',$ntC); $ntOpts = $options[$ntA[0]][$ntA[1]]; 
        if (!empty($ntOpts) && is_array($ntOpts)) { $logNT = $ntA[0];  $clName = 'nxs_class_SNAP_'.strtoupper($logNT);                  
          $logNT = '<span style="color:#800000">'.strtoupper($logNT).'</span> - '.$ntOpts['nName'];      
          $ntOpts['postType'] = $_POST['mType']; 
          //## Make Post
          $ntToPost = new $clName(); $ret = $ntToPost->doPostToNT($ntOpts, $message);      
          if (!is_array($ret) || $ret['isPosted']!='1') { //## Error 
             $postResults .= $logNT ." - Error ".print_r($ret, true)."<br/>";
          } else {  // ## All Good           
             if (!empty($ret['postURL'])) $extInfo = '<a href="'.$ret['postURL'].'" target="_blank">Post Link</a>'; 
             $postResults .= $logNT ." - OK - ".$extInfo."<br/>"; 
          }
        }
    } echo "Done. Results:<br/> ".$postResults; }      die();
  }
?>
<html>  
  <head>    
    <title>NextScripts SNAP New Post</title>    
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">    
    <style type="text/css">      
    .nxspButton:hover { background-color: #1E1E1E;}
  .nxspButton { background-color: #2B91AF; color: #FFFFFF; cursor: pointer; display: inline-block; text-align: center; text-decoration: none; border-radius: 6px 6px 6px 6px; box-shadow: none; font: bold 131% sans-serif; padding: 0 6px 2px; position: absolute; right: -7px; top: -7px;}
  #nxs_spPopup, #nxs_popupDiv, #showLicForm{ min-height: 250px; z-index:999991; background-color: #FFFFFF; border-radius: 5px 5px 5px 5px;  box-shadow: 0 0 3px 2px #999999; color: #111111; display: none;  min-width: 850px; padding: 25px;}
  #nxsNewSNPost .nxsNPLabel {position: relative;}
  #nxsNewSNPost .nxsNPRow {position: relative; padding: 8px;}
  #nxsNewSNPost input {position: relative; font-size: 16px;}
  .nsx_iconedTitle {font-size: 17px; font-weight: bold; margin-bottom: 15px; padding-left: 20px; background-repeat: no-repeat; }
  .nxsNPRowSm, .nxsNPRow .nsx_iconedTitle {font-size: 12px; }
  .nxsNPLeft, .nxsNPRight {position: relative; float: left;}
  .nxsNPLeft {width: 40%;} .nxsNPRight {width: 60%;}
    </style>    
    <script src="//code.jquery.com/jquery-1.11.2.min.js"></script>
    <script type="text/javascript"> var ajaxurl = '';
      function nxs_doNP(){ 
          jQuery("#nxsNPLoaderPost").show();  var mNts = []; 
          jQuery('input[name=nxsNPNts]:checked').each(function(i){ mNts[i] = jQuery(this).val(); });
          jQuery.post(ajaxurl,{action: 'nxs_snap_aj',"nxsact":"doNewPost", mText: jQuery('#nxsNPText').val(), mTitle: jQuery('#nxsNPTitle').val(), mType: jQuery('input[name=nxsNPType]:checked').val(), mLink: jQuery('#nxsNPLink').val(), mImg: jQuery('#nxsNPImg').val(), mNts: mNts, nxs_mqTest:"'", _wpnonce: jQuery('#nxsSsPageWPN_wpnonce').val()}, 
            function(j){  
                jQuery("#nxsNPResult").html(j); 
                jQuery("#nxsNPLoaderPost").hide(); 
                jQuery("#nxsNPCloseBt").val('Close'); 
            }, "html")
      }
    </script>
  </head>
<body>  
<div id="nxsNewSNPost" style="width: 880px;">
   <div>
      <h2>New Post to the Configured Social Networks</h2>
   </div>
   <div class="nxsNPRow"><label class="nxsNPLabel">Title (Will be used where possible):</label><br/><input id="nxsNPTitle" type="text" size="80"></div>
   <div class="nxsNPRow"><label class="nxsNPLabel">Message:</label><br/><textarea id="nxsNPText" name="textarea" cols="90" rows="8"></textarea></div>
   <div class="nxsNPRow">
      <label class="nxsNPLabel">Post Type:</label>
      <br/>
      <input type="radio" name="nxsNPType"  id="nxsNPTypeT" value="T" checked="checked" /><label class="nxsNPRowSm">Text Post</label><br/>    
      <br/>
      <input type="radio" name="nxsNPType"  id="nxsNPTypeL" value="A"><label class="nxsNPRowSm">Link Post</label>
      <div class="nxsNPRowSm">
        <label class="nxsNPLabel">URL (Will be attached where possible, text post will be made where not):</label>
        <br/>
        <input id="nxsNPLink" onfocus="jQuery('#nxsNPTypeL').attr('checked', 'checked')" type="text" size="80" />
      </div>
      <br/><input type="radio" name="nxsNPType" id="nxsNPTypeI" value="I"><label class="nxsNPRowSm">Image Post</label>
      <div class="nxsNPRowSm">
        <label class="nxsNPLabel">Image URL (Will be used where possible, text post will be made where not):</label>
        <br/>
        <input id="nxsNPImg" onfocus="jQuery('#nxsNPTypeI').attr('checked', 'checked')" type="text" size="80" />
      </div>
   </div>
   <div class="nxsNPRow">
      <div class="nxsNPLeft" style="display: inline-block;">
         <div id="nxsNPLoaderPost" style="display: none";>Posting...., it could take some time...  </div>
         <div class="submitX"><input style="font-weight: bold; width: 70px;" type="button" onclick="nxs_doNP();" value="Post">
            <?php if ($air) { ?>&nbsp;&nbsp;&nbsp;&nbsp;<input id="nxsNPCloseBt" style="width: 70px;" class="bClose" type="button" value="Cancel"> <?php } ?>
         </div>
         <div id="nxsNPResult">&nbsp;</div>
      </div>
      <div class="nxsNPRight">
         <div class="nxsNPRow">
            <div style="float: right; font-size: 12px;" >
               <a href="#" onclick="jQuery('.nxsNPDoChb').attr('checked','checked'); return false;"><?php  _e('Check All', 'nxs_snap'); ?></a>&nbsp;<a href="#" onclick="jQuery('.nxsNPDoChb').removeAttr('checked'); return false;"><?php _e('Uncheck All', 'nxs_snap'); ?></a>
            </div>
            <label class="nxsNPLabel">Networks:</label><br/> 
            <div class="nxsNPRow" style="font-size: 12px;">
               <?php 
                  foreach ($nxs_snapAPINts as $avNt) { 
                    if ( isset($options[$avNt['lcode']]) && count($options[$avNt['lcode']])>0) { ?>  
               <div class="nsx_iconedTitle" style="margin-bottom:1px;background-image:url(<?php echo $nxs_plurl;?>img/<?php echo $avNt['lcode']; ?>16.png);"><?php echo $avNt['name']; ?><br/>
                  <?php $ntOpts = $options[$avNt['lcode']]; foreach ($ntOpts as $indx=>$pbo){ ?>
                  <input class="nxsNPDoChb" value="<?php echo $avNt['lcode']; ?>--<?php echo $indx; ?>" name="nxsNPNts" type="checkbox" <?php if ((int)$pbo['do'.$avNt['code']] == 1 && $pbo['catSel']!='1') echo "checked"; ?> /> 
                  <?php echo $avNt['name']; ?> <i style="color: #005800;"><?php if($pbo['nName']!='') echo "(".$pbo['nName'].")"; ?></i></br>
                  <?php  }  ?>
               </div>
               <?php  
                  } } ?> 
            </div>
         </div>
      </div>
   </div>
</div>
</body>
<?php ?>