<?php //## NextScripts SNAP API 4.2.1
//================================GOOGLE========================================
if (!class_exists('nxsAPI_GP')){ class nxsAPI_GP{ var $ck = array(); var $debug = false; var $proxy = array(); var $at=''; var $pig='x';
    function headers($ref, $org='', $type='GET', $aj=false){  $hdrsArr = array(); 
      $hdrsArr['Cache-Control']='max-age=0'; $hdrsArr['Connection']='keep-alive'; $hdrsArr['Referer']=$ref;
      $hdrsArr['User-Agent']='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36'; 
      
      if($type=='JSON') $hdrsArr['Content-Type']='application/json;charset=UTF-8'; elseif($type=='POST') $hdrsArr['Content-Type']='application/x-www-form-urlencoded'; 
        elseif($type=='JS') $hdrsArr['Content-Type']='application/javascript; charset=UTF-8'; elseif($type=='PUT') $hdrsArr['Content-Type']='application/octet-stream';
      if($aj===true) $hdrsArr['X-Requested-With']='XMLHttpRequest';  if ($org!='') $hdrsArr['Origin']=$org; 
      if ($type=='GET') $hdrsArr['Accept']='text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'; else $hdrsArr['Accept']='*/*';
      if (function_exists('gzdeflate')) $hdrsArr['Accept-Encoding']='deflate,sdch'; 
      $hdrsArr['Accept-Language']='en-US,en;q=0.8'; return $hdrsArr;         
    }
    function prcGSON($gson){ $json = substr($gson, 5); $jsonx=json_decode($json, true); if (!empty($jsonx)) return $json;  
      $json = str_replace("\r",'',$json); $json = str_replace("\n",'',$json); $json = str_replace(',{',',{"',$json); $json = str_replace(':[','":[',$json); $json = str_replace(',{""',',{"',$json); $json = str_replace('"":[','":[',$json); 
      $json = str_replace('[,','["",',$json); $json = str_replace(',,',',"",',$json); $json = str_replace(',,',',"",',$json); return $json; 
    }
    function check($srv, $u){ $ck = $this->ck;  if (!empty($ck) && is_array($ck)) {  if ($this->debug) echo "[G] Checking ".$srv." ;<br/>\r\n";  // prr($ck); //die();
        if ($srv=='GP') { $hdrsArr = $this->headers('https://plus.google.com/'); $url = 'https://plus.google.com/';}
        if ($srv=='YT') { $hdrsArr = $this->headers('https://www.youtube.com/'); $url = 'https://www.youtube.com/feed/subscriptions';}        
        if ($srv=='BG') { $hdrsArr = $this->headers('https://www.blogger.com/'); $url = 'https://www.blogger.com/user-settings.g';}        
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get($url, $advSet); //prr($rep);
        if (is_nxs_error($rep)) return false; if ($rep['response']['code']=='302' && stripos($rep['headers']['location'], 'accounts.google.com')!==false) return false;
        if (stripos($rep['body'], $u)===false) return false; return true;                    
    } return false; }
    function connect($u,$p,$srv='GP'){ $sslverify = true; $oldBr = 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)';
      if (!$this->check($srv, $u)){ if ($this->debug) echo "[GP] NO Saved Data; Logging in...<br/>\r\n"; if ($this->debug) echo "[".$srv."] L to: ".$srv."<br/>\r\n";
        $err = nxsCheckSSLCurl('https://www.google.com'); if ($err!==false && $err['errNo']=='60') $sslverify = false;          
        $hdrsArr = $this->headers('https://www.google.com/'); $hdrsArr['User-Agent']=$oldBr; $advSet = nxs_mkRemOptsArr($hdrsArr, '', '', $this->proxy); $rep = nxs_remote_get('https://plus.google.com', $advSet); $ck = $rep['cookies'];        
        if ($srv == 'GP') $lpURL = 'https://accounts.google.com/ServiceLogin?service=oz&continue=https://plus.google.com/?gpsrc%3Dogpy0%26tab%3DwX%26gpcaz%3Dc7578f19&hl=en-US'; 
        if ($srv == 'YT') $lpURL = 'https://accounts.google.com/ServiceLogin?service=oz&checkedDomains=youtube&checkConnection=youtube%3A271%3A1%2Cyoutube%3A69%3A1&continue=https://www.youtube.com/&hl=en-US';   
        if ($srv == 'BG') $lpURL = 'https://accounts.google.com/ServiceLogin?service=blogger&passive=1209600&continue=https://www.blogger.com/home&followup=https://www.blogger.com/home&ltmpl=start';
        $hdrsArr = $this->headers('https://accounts.google.com/'); $hdrsArr['User-Agent']=$oldBr; $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get($lpURL, $advSet); 
        if (is_nxs_error($rep)) {  $badOut = "|ERROR LOGIN GL-1 -".print_r($rep, true); return $badOut; } $ck = nxs_MergeCookieArr($ck, $rep['cookies']); $contents = $rep['body']; //if ($this->debug) prr($contents); 
        //## GET HIDDEN FIELDS
        $md = array(); $flds  = array();
        while (stripos($contents, '<input')!==false){ $inpField = trim(CutFromTo($contents,'<input', '>')); $name = trim(CutFromTo($inpField,'name="', '"'));
          if ( stripos($inpField, '"hidden"')!==false && $name!='' && !in_array($name, $md)) { $md[] = $name; $val = trim(CutFromTo($inpField,'value="', '"')); $flds[$name]= $val;}
          $contents = substr($contents, stripos($contents, '<input')+8);          
        } $flds['Email'] = $u; $flds['Passwd'] = $p;  $flds['signIn'] = 'Sign%20in'; $flds['PersistentCookie'] = 'yes'; $flds['rmShown'] = '1'; $flds['pstMsg'] = '1'; 
        if (empty($flds['GALX']))$flds['GALX'] = ''; if (empty($flds['gxf']))$flds['gxf'] = ''; $lURL = 'https://accounts.google.com/ServiceLoginAuth'; // $flds['bgresponse'] = $bg;
        $flds2 = array('Email'=>$u, 'requestlocation'=>'https://accounts.google.com/ServiceLogin?passive=1209600&osid=1&continue=https://plus.google.com/collections/featured&followup=https://plus.google.com/collections/featured#identifier', 'bgresponse'=>'','Page'=>'PasswordSeparationSignIn','GALX'=> $flds['GALX'],'gxf'=>$flds['gxf'],'continue'=>'https://plus.google.com/collections/featured','followup'=>'https://plus.google.com/collections/featured','osid'=>'1','_utf8'=>urldecode('%E2%98%83'),'pstMsg'=>'1','checkConnection'=>'youtube:323:1','checkedDomains'=>'youtube','rmShown'=>'1'); //prr($flds2);
        $hdrsArr = $this->headers($flds2['requestlocation'], 'https://accounts.google.com', 'POST');  $hdrsArr['User-Agent']=$oldBr; $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $flds2, $this->proxy); $rep = nxs_remote_post('https://accounts.google.com/_/signin/v1/lookup', $advSet); 
        if (is_nxs_error($rep)) {  $badOut = "|ERROR GL-3 - ".print_r($rep, true); return $badOut; } //prr($advSet);  prr($rep);// die();// $ck = $rep['cookies'];
        if ( !empty($rep['body']) && stripos($rep['body'], 'ShadowDisambiguate')!==false ) return "Incompatibility detected. You have two google accounts with the same email."; //prr($rep);
        if ($rep['response']['code']=='200' && !empty($rep['body'])) { $jss = json_decode($rep['body'], true); //prr($jss);
          if (!empty($jss['action']) && $jss['action']=='REDIRECT' && !empty($jss['redirectUrl'])) {  $la = explode('?',$jss['redirectUrl']); $lURL =$la[0]; $chID = '1'; if (stripos($jss['redirectUrl'], '/pwd/')!==false) $chID = CutFromTo($jss['redirectUrl'],'/pwd/','?'); 
            $hdrsArr = $this->headers('https://www.google.com/'); $hdrsArr['User-Agent']=$oldBr; $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get($jss['redirectUrl'], $advSet); $contents = $rep['body']; $gfx = CutFromTo($contents,'id="gxf" value="','"');          
            $flds = array('challengeId'=>$chID,'challengeType'=>'1','continue'=>'https://plus.google.com/collections/featured','osid'=>'1','checkedDomains'=>'youtube','checkConnection'=>'youtube:323:1','pstMsg'=>'1','TL'=> urldecode(CutFromTo($la[1].'&','&TL=','&')),'gxf'=>$gfx,'Email'=>$u,'Passwd'=>$p);
          }
          if (!empty($jss['action']) && $jss['action']=='ASK_PASSWORD') $lURL ='https://accounts.google.com/signin/challenge/sl/password';
          if (!empty($jss['action']) && $jss['action']=='SHOW_CAPTCHA') return "You have exceeded the number of allowed login attempts. Please try again in 30 minutes.";
        } //## ACTUAL LOGIN    
        $hdrsArr = $this->headers($lpURL, 'https://accounts.google.com', 'POST');  $hdrsArr['User-Agent']=$oldBr; $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $flds, $this->proxy); 
        $rep = nxs_remote_post($lURL, $advSet); if (is_nxs_error($rep)) {  $badOut = "|ERROR GL-3 - ".print_r($rep, true); return $badOut; } $ck = $rep['cookies']; /* $rep['body']=''; */ // prr($lURL);  prr($advSet); prr($rep);    die();
         
        $unlockCaptchaMsg = "Your Google+ account is locked for the new applications to connect. Please follow this <a href='http://nxs.fyi/g-unlock' target='_blank'>instructions to unlock Google</a>";
        $twoStepVerMsg = '<b style="color:#800000;">2-Step Verification is on. </b>2-Step Verification is not compatible with auto-posting. Please see more here:<br/> <a href="http://nxs.fyi/google-2step" target="_blank">Google+, 2-step verification and auto-posting</a><br/>';
        if ($rep['response']['code']=='200' && !empty($rep['body'])) { $rep['body'] = str_ireplace('\'CREATE_CHANNEL_DIALOG_TITLE_IDV_CHALLENGE\': "Verify your identity"', "", $rep['body']);
          $rep['body'] = str_ireplace('class="error-msg" id="errormsg_0_Email"></span>','',$rep['body']); $rep['body'] = str_ireplace('class="error-msg" id="errormsg_0_Passwd"></span>','',$rep['body']); 
          if (stripos($rep['body'],'class="captcha-box"')!==false || stripos($rep['body'],'name="logincaptcha"')!==false || stripos($rep['body'],'is that really you')!==false || stripos($rep['body'],'Verify your identity')!==false) return $unlockCaptchaMsg;
          if (stripos($rep['body'],'class="error-msg"')!==false) { $ret = trim(strip_tags(CutFromTo(CutFromTo($rep['body'],'class="error-msg"','</span>').'||', '>', '||')));  if (!empty($ret))  return '|Error Google (LOGIN) - '.$ret;
            if (trim($ret)==""){ $rep['body']=CutFromTo($rep['body'],'class="error-msg"','</html>'); return '|Error GL-4 - '.strip_tags(CutFromTo(CutFromTo($rep['body'],'class="error-msg"','</span>').'||', '>', '||'));}
          } 
        } // prr($lURL); prr($rep); prr($advSet);
        if ($rep['response']['code']=='302' && !empty($rep['headers']['location'])){ $repLoc = $rep['headers']['location']; if(stripos($repLoc, 'ServiceLoginAuth')!==false || stripos($repLoc, 'ServiceLogin')!==false) return 'Incorrect Username/Password ';
          if((stripos($repLoc, 'LoginVerification')!==false || stripos($repLoc, '/selectchallenge')!==false)) return $unlockCaptchaMsg;
          if(( stripos($repLoc, '/SmsAuth')!==false || stripos($repLoc, '/SecondFactor')!==false)) return $twoStepVerMsg;          
          if(( stripos($repLoc, '/challenge')!==false)) return "Can't login. Two possible reasons: <br/>1. ".$twoStepVerMsg."<br/>2. ".$unlockCaptchaMsg;
          if ($srv == 'BG') $repLoc = 'https://accounts.google.com/CheckCookie?checkedDomains=youtube&checkConnection=youtube%3A170%3A1&pstMsg=1&chtml=LoginDoneHtml&service=blogger&continue=https%3A%2F%2Fwww.blogger.com%2Fhome&gidl=CAA'; 
          if ($srv == 'YT') $repLoc = 'https://accounts.google.com/CheckCookie?hl=en-US&checkedDomains=youtube&checkConnection=youtube%3A271%3A1%2Cyoutube%3A69%3A1&pstMsg=1&chtml=LoginDoneHtml&service=oz&continue=https%3A%2F%2Fwww.youtube.com%2F&gidl=CAA';
          if ($srv == 'GP') $repLoc = 'https://accounts.google.com/CheckCookie?hl=en-US&checkedDomains=youtube&checkConnection=youtube%3A179%3A1&pstMsg=1&chtml=LoginDoneHtml&service=oz&continue=https%3A%2F%2Fplus.google.com%2F%3Fgpsrc%3Dogpy0%26tab%3DwX%26gpcaz%3Dc7578f19&gidl=CAA';           
          //if ($srv == 'GP') $repLoc = 'https://accounts.google.com/CheckCookie?checkedDomains=youtube&checkConnection=youtube%3A323%3A1&pstMsg=1&osid=1&chtml=LoginDoneHtml&continue=https%3A%2F%2Fplus.google.com%2Fcollections%2Ffeatured&gidl=EgIIAA';
          if ($this->debug) echo "[".$srv."] R to: ".$repLoc."<br/>\r\n";  $hdrsArr = $this->headers($lpURL, 'https://accounts.google.com');    $hdrsArr['User-Agent']=$oldBr;        
          $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get($repLoc, $advSet);  $ck = nxs_MergeCookieArr($ck, $rep['cookies']); if ($rep['response']['code']=='400') return 'Error #21. Bad Username/password';   //prr($rep);
          if (!is_nxs_error($rep) && $srv == 'YT' && $rep['response']['code']=='302' && !empty($rep['headers']['location'])) { $repLoc = $rep['headers']['location'];        if ($this->debug) echo "[".$srv."] R to: ".$repLoc."<br/>\r\n";       
            $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get($repLoc, $advSet); $ck = $rep['cookies'];                          
          } if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR 4="; return $badOut; } $contents = $rep['body']; $rep['body'] = '';          
          //## BG Auth redirect          
          if ($srv != 'GP' && stripos($contents, 'meta http-equiv="refresh"')!==false) {$rURL = htmlspecialchars_decode(CutFromTo($contents,';url=','"')); 
            if ($this->debug) echo "[".$srv."] R to: ".$rURL."<br/>\r\n";  $hdrsArr = $this->headers($repLoc); $hdrsArr['User-Agent']=$oldBr; $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get($rURL, $advSet);//  prr($rep);
            if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR 5="; return $badOut; } $ck = $rep['cookies'];
            if (!empty($rep['headers']['location'])) { $rURL = $rep['headers']['location']; $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get($rURL, $advSet);
              if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR 6="; return $badOut; }              
              if (!empty($rep['headers']['location'])) { $rURL = $rep['headers']['location'];  $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get($rURL, $advSet); 
                if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR 7="; return $badOut; }
              } if (!empty($rep['cookies'])) $ck = $rep['cookies'];
            } if (!empty($rep['cookies'])) $ck = $rep['cookies'];
          } $this->ck = $ck; if ($this->debug) echo "[GP] Login OK;<br/>\r\n"; return false;  
        } return 'Unexpected Error ('.$rep['response']['code'].'), Please contact support';  
      } else { if ($this->debug) echo "[GP] Saved Data is OK;<br/>\r\n"; return false; }
    }
    function getAt($url='https://plus.google.com/discover', $ck='') { if (!empty($this->at)) return true; if (empty($ck)) $ck = $this->ck;
      $hdrsArr = $this->headers('');  $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy, 0); $rep = nxs_remote_get($url, $advSet); // prr($url); prr($advSet); prr($rep);
      if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR CSI"; return $badOut; } $contents = $rep['body']; 
      if (stripos($contents,'window.IJ_values = ')!==false) { $pig = CutFromTo($contents, 'window.IJ_values = ', ',];').']'; $pig = str_replace("'",'"',$pig); $pig = str_replace('\x2','',$pig); 
        $pig = str_replace('\x3','',$pig); $pig = json_decode($pig, true); for ($k = 31; $k<45; $k++) if (!empty($pig[$k]) && is_numeric($pig[$k]) && $pig[$k]>1177680286367) { $this->pig = $pig[$k]; break;}
      } if (stripos($contents,'"SNlM0e":"')!==false) $at = CutFromTo($contents, '"SNlM0e":"', '",'); else return "Error (NXS): Lost Login info. Please see FAQ #3.4 or contact support";
      $this->at = $at; return true;
    }
    function urlInfo($url){ $out['link'] = $url; $url = urlencode($url); $at="623482169132-88"; $sslverify = false; $ck = $this->ck; $res = $this->getAt(); if ($res!==true) return $res; else $at = $this->at;
      $spar='f.req=%5B%5B%5B92371866%2C%5B%7B%2292371866%22%3A%5B%22'.$url.'%22%2C%5B%5B73046798%5D%2C%5B%5D%5D%2C1%5D%7D%5D%2Cnull%2Cnull%2C0%5D%5D%5D&at='.urlencode($at)."&";      
      $gurl='https://plus.google.com/_/PlusAppUi/data?ds.extension=92371866&hl=en&soc-app=199&soc-platform=1&soc-device=1&_reqid=7372229&rt=c';
      $hdrsArr = $this->headers('https://plus.google.com/', 'https://plus.google.com', 'POST', true); $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $spar, $this->proxy);
      $rep = nxs_remote_post($gurl, $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR"; return $badOut; } $contents = $rep['body']; if (stripos($contents, '://')===false) return '';
      if (stripos($contents,',[["')!==false)  $out['img'] = CutFromTo($contents, ',[["', '",');
      return $out;
    }     
    function getPgsList($pgID){ $pgs = ''; $sslverify = false; $ck = $this->ck; $hdrsArr = $this->headers('https://accounts.google.com', 'https://accounts.google.com');  
      $gUrl = 'https://accounts.google.com/ServiceLogin?service=accountsettings&passive=1209600&osid=1&continue=https://myaccount.google.com/?authuser%3D0&followup=https://myaccount.google.com/?authuser%3D0&authuser=0';
      $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $repG = nxs_remote_get($gUrl, $advSet); 
      if ($repG['response']['code']=='302' && !empty($repG['headers']['location'])){ $hdrsArr = $this->headers($gUrl);  $gUrl = $repG['headers']['location'];
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $repG = nxs_remote_get($gUrl, $advSet); $ck = nxs_MergeCookieArr($ck,  $repG['cookies']);
      } $hdrsArr = $this->headers('https://plus.google.com/'); $this->at=''; $ck = nxsDelCookie($ck,'LSID'); $ck = nxsDelCookie($ck,'GAPS'); $ck = nxsDelCookie($ck,'ACCOUNT_CHOOSER'); $ck = nxsDelCookie($ck,'CONSENT');      
      $this->getAt('https://myaccount.google.com/brandaccounts', $ck); $at = $this->at;  $this->at='';  
      $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, 'f.req=%5B%5B%5B106277917%2C%5B%7B%22106277917%22%3A%5B%5B1000%5D%2C1%2Cfalse%2Cfalse%5D%7D%5D%2Cnull%2Cnull%2C0%5D%5D%5D&at='.urlencode($at).'&', $this->proxy, 1);
      $rep = nxs_remote_post('https://myaccount.google.com/_/AccountSettingsUi/data?ds.extension=106277917', $advSet);
      if (is_nxs_error($rep)) return false; if (!empty($rep['cookies'])) $ck = $rep['cookies']; $contents = $rep['body']; $code = json_decode($this->prcGSON($contents), true);
      if (!empty($code) && is_array($code) && !empty($code[0]) && is_array($code[0]) && !empty($code[0][2]) && is_array($code[0][2])) $code = $code[0][2];      
      $k = array_keys($code); $code = $code[$k[0]]; if (!empty($code) && is_array($code) && !empty($code[0]) && is_array($code[0])) $code = $code[0];      
      if (!empty($code)) { $pgs .= '<option disabled>Pages</option>'; foreach ($code as $cd) {
        $name = $cd[1]; $id = $cd[0]; $pgs .= '<option class="nxsBlue" '.($pgID==$id?'selected="selected"':'').' value="'.$id.'">&nbsp;&nbsp;&nbsp;'.$name.' ('.$id.')</option>';
      }} return $pgs;
    } 
    function getWhereToPostList($currPg, $currPstAs){ $items = ''; $currPstAs = (!empty($currPstAs)&&$currPstAs!='p')?'/b/'.$currPstAs:'';
      $items .= $this->_getCollCmns($currPg, 'https://plus.google.com'.$currPstAs.'/collections/yours', 'Collections','c',0,0); $items .= $this->_getCollCmns($currPg, 'https://plus.google.com'.$currPstAs.'/communities/yours', 'Communities you moderate','m',0,1,true,'c'); 
      $items .= $this->_getCollCmns($currPg, 'https://plus.google.com'.$currPstAs.'/communities/member', 'Communities you\'ve joined</option>', 'm',0,1,true,'c'); return $items;
    }    
    function _getCollCmns($pgID, $url, $label, $num, $pthNm1, $pthNm2, $isZero=false, $let=''){ $items = ''; $ck = $this->ck;
      $hdrsArr = $this->headers('https://plus.google.com/'); $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get($url, $advSet); // prr($url); //prr($rep); prr($advSet); die();
      if (is_nxs_error($rep)) return false; if (!empty($rep['cookies'])) $ck = $rep['cookies']; $contents = CutFromTo($rep['body'], 'AF_initDataCallback({','</body>');// prr($contents);
      if ($num=='c') $code = '[[[['.CutFromTo($contents, '[[[[', '}});');  elseif ($num=='m') $code = '[[0,[[['.CutFromTo($contents, ',[[[', '}});'); 
      $code = json_decode($code, true); if (!empty($code) && is_array($code) && !empty($code[$pthNm1]) && is_array($code[$pthNm1]) && !empty($code[$pthNm1][$pthNm2]) && is_array($code[$pthNm1][$pthNm2])) { $code = $code[$pthNm1][$pthNm2];
         $items .= '<option disabled>'.$label.'</option>'; foreach ($code as $cd) { if ($isZero) $cd=$cd[0]; // prr($cd);
          $name = $cd[1]; $id = $cd[0]; $items .= '<option class="nxsGreen" '.($pgID==($let.$id)?'selected="selected"':'').' value="'.$let.$id.'">&nbsp;&nbsp;&nbsp;'.$name.'</option>';
      }} return $items;
    }    
    function getCCatsGP($commPageID, $currCat=''){ $items = '';   $sslverify = false; $ck = $this->ck;
      $hdrsArr = $this->headers('https://plus.google.com/'); $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get('https://plus.google.com/communities/'.$commPageID, $advSet); 
      if (is_nxs_error($rep)) return false; if (!empty($rep['cookies'])) $ck = $rep['cookies']; $contents = $rep['body']; $tmps = CutFromTo($contents,'"'.$commPageID.'",[["','}});'); $commPageID2 = '[[["'.stripslashes(str_replace('\n', '', $tmps));
      $commPageID2 = str_replace('\u0026','&',$commPageID2); $commPageID2 = json_decode($commPageID2); // prr($commPageID2);
      if (is_array($commPageID2) && !empty($commPageID2[0]) && is_array($commPageID2[0])) foreach ($commPageID2[0] as $cpiItem) if (is_array($cpiItem)) { 
          $val = $cpiItem[0]; $name = $cpiItem[1]; $items .= '<option '.(!empty($currCat)&&$currCat==$val?'selected="selected" ':'').'value="'.$val.'">'.$name.'</option>'; 
      } return $items;   
    }
    
    function chckForCpt($content, $ck){ if(stripos($content, 'action="/das_captcha"')!==false) { global $nxs_plurl;
      $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $ca = nxs_remote_get('https://www.google.com/recaptcha/api/noscript?k=6LcV18ESAAAAAI1Z5NnSAUBz4pTj3hYJQ_6NLgFN', $advSet); 
      if (is_nxs_error($ca)) {  $badOut = print_r($ca, true)." - [captcha] ERROR"; return $badOut; } $img = CutFromTo($ca['body'], 'src="image?c=', '"'); 
      $formcode = '<form '.CutFromTo($content, '<form method="post" action="/das_captcha"', '</form>');  $formcode = str_ireplace('</iframe>', '', $formcode); 
      $formcode = str_ireplace('<iframe src="//www.google.com/recaptcha/api/noscript?k=6LcV18ESAAAAAI1Z5NnSAUBz4pTj3hYJQ_6NLgFN" height="300" width="500" frameborder="0"', $ca['body'], $formcode);
      $img = '<img style="display:block;" alt="reCAPTCHA challenge image" height="57" width="300" src="'.$nxs_snapSetPgURL.'?pg=nxs&ca='.$img.'"/>';    
      echo "Google asked you to enter Captcha. Please type the two words separated by a space (not case sensitive) and click \"Continue\"";
      echo $img; echo '<br/><input value="" style="width: 30%;" id="nxs_cpt_val" name="nxs_cpt" /><input type="hidden" id="nxsLiNum" name="nxsLiNum" value="'.$iidb.'" /><input type="button" value="Continue" onclick="doCtpSave(); return false;" id="results_ok_button" name="nxs_go" class="button" />'; ?><script type="text/javascript">       
        function doCtpSave(){ var u = jQuery('#nxs_cpt_val').val(); var ii = jQuery('#nxsLiNum').val(); //alert(ii);       
          var style = "position: fixed; display: none; z-index: 1000; top: 50%; left: 50%; background-color: #E8E8E8; border: 1px solid #555; padding: 15px; width: 350px; min-height: 80px; margin-left: -175px; margin-top: -40px; text-align: center; vertical-align: middle;";
          jQuery('body').append("<div id='test_results' style='" + style + "'></div>");
          jQuery.post(ajaxurl,{c:u, i:ii, action: 'nxs_snap_aj',"nxsact":'nxsCptCheckGP', id: 0, _wpnonce: jQuery('#nxsSsPageWPN_wpnonce').val()}, function(j){
            jQuery('#test_results').html('<p> ' + j + '</p>' +'<input type="button" class="button" onclick="jQuery(\'#test_results\').hide();" name="results_ok_button" id="results_ok_button" value="OK" />'); jQuery('#test_results').show(); 
          }, "html")
        }</script> <?php echo "||".$formcode."||";
        while (stripos($formcode, '"hidden"')!==false){$formcode = substr($formcode, stripos($formcode, '"hidden"')+8); $name = trim(CutFromTo($formcode,'name="', '"')); $md = array();
          if (!in_array($name, $md)) { $md[] = $name; $val = trim(CutFromTo($formcode,'value="', '"')); $flds[$name]= $val;      $mids .= "&".$name."=".$val;}
        } $ser = array(); $ser['c'] = $ck; $ser['f'] = $flds; $seForDB = serialize($ser); session_id("nxs-temp-gpcpt"); session_start(); $_SESSION["nxs-temp-gpcpt"] = $seForDB;
      }
    }
    function postGP($msg, $lnk='', $pageID='', $commOrColID='', $commPageCatID=''){ $rnds = rndString(12); $sslverify = false; $ck = $this->ck; $hdrsArr = $this->headers('');
      $pageID = trim($pageID); $commOrColID = trim($commOrColID); $ownerID = ''; $bigCode = '';  $isPostToPage = $pageID!=''; $commCatIDorColName = 'mypage';
      if (function_exists('nxs_decodeEntitiesFull')) $msg = nxs_decodeEntitiesFull($msg); if (function_exists('nxs_html_to_utf8')) $msg = nxs_html_to_utf8($msg);
      $msg = str_replace('<br>', "_NXSZZNXS_5Cn", $msg); $msg = str_replace('<br/>', "_NXSZZNXS_5Cn", $msg); $msg = str_replace('<br />', "_NXSZZNXS_5Cn", $msg);     
      $msg = str_replace("\r\n", "\n", $msg); $msg = str_replace("\n\r", "\n", $msg); $msg = str_replace("\r", "\n", $msg); $msg = str_replace("\n", "_NXSZZNXS_5Cn", $msg);  $msg = str_replace('"', '\"', $msg); 
      $msg = urlencode(strip_tags($msg)); $msg = str_replace("_NXSZZNXS_5Cn", "%5Cn", $msg);  
      $msg = str_replace('+', '%20', $msg); $msg = str_replace('%0A%0A', '%20', $msg); $msg = str_replace('%0A', '', $msg); $msg = str_replace('%0D', '%5C', $msg);
      if (!empty($lnk) && !is_array($lnk)) $lnk = $this->urlInfo($lnk); if ($lnk=='') $lnk = array('img'=>'', 'link'=>'', 'fav'=>'', 'domain'=>'', 'title'=>'', 'txt'=>'');
      if (!isset($lnk['link']) && !empty($lnk['img'])) { $hdrsArr = $this->headers(''); unset($hdrsArr['Connection']);  $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get($lnk['img'], $advSet); 
        if (is_nxs_error($rep)) $lnk['img']=''; elseif ($rep['response']['code']=='200' && !empty($rep['headers']['content-type']) && stripos($rep['headers']['content-type'],'text/html')===false) {    
          if (!empty($rep['headers']['content-length']))  $imgdSize = $rep['headers']['content-length'];
          if ((empty($imgdSize) || $imgdSize == '-1') && !empty($rep['headers']['size_download'])) $imgdSize = $rep['headers']['size_download'];
          if ((empty($imgdSize) || $imgdSize == '-1')){ $ch = curl_init($lnk['img']); curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); curl_setopt($ch, CURLOPT_HEADER, TRUE); curl_setopt($ch, CURLOPT_NOBODY, TRUE);
            $data = curl_exec($ch);  $imgdSize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD); curl_close($ch);  
          } 
          if ((empty($imgdSize) || $imgdSize == '-1')) $imgdSize =  strlen($rep['body']);
          $urlParced = pathinfo($lnk['img']); $remImgURL = $lnk['img']; $remImgURLFilename = nxs_mkImgNm(nxs_clFN($urlParced['basename']), $rep['headers']['content-type']);  $imgData = $rep['body'];        
        } else $lnk['img']=''; 
      }
      if (isset($lnk['img'])) $lnk['img'] = urlencode($lnk['img']); if (empty($lnk['img'])) $lnk['img'] = ''; if (isset($lnk['link'])) $lnk['link'] = urlencode($lnk['link']); 
      $refPage = 'https://plus.google.com/b/'.$pageID.'/'; $rndReqID = rand(1203718, 647379);      
      $pgInf = (!empty($pageID))?'b/'.$pageID.'/':''; $gpp = 'https://plus.google.com/'.$pgInf.'_/PlusAppUi/mutate?ds.extension=79255737&hl=en&soc-app=199&soc-platform=1&soc-device=1&_reqid='.$rndReqID.'&rt=c'; 
      $res = $this->getAt(); if ($res!==true) return $res; else $at = $this->at; $gNum = '94316911'; $comOrPg  = '1%5D%2C%22Public%22'; // $commCatIDorColName = 'My%20Things';
      if (!empty($commOrColID) && strlen($commOrColID)>10 && !empty($commPageCatID)) $comOrPg = 'null%2C%5B%22'.$commOrColID.'%22%2Cnull%2C%22'.$commPageCatID.'%22%5D%5D%2C%22!%22%2Cnull%2Cnull%2Cnull%2C%22!%22';
        elseif (!empty($commOrColID) && strlen($commOrColID)<10) $comOrPg = 'null%2Cnull%2Cnull%2C%5B%22'.$commOrColID.'%22%2C%22collexions%22%5D%5D%2C%22'.$commCatIDorColName.'%22';  //    prr($comOrPg);
      $spar = "f.req=%5B%22af.maf%22%2C%5B%5B%22af.add%22%2C79255737%2C%5B%7B%2279255737%22%3A%5B%5B%5B%5D%2C%5B%5D%2C%5B%5B%5Bnull%2Cnull%2C".$comOrPg."%5D%5D%5D%2C%5B%5B%5B0%2C%22".$msg."%22%2Cnull%5D%5D%5D%2Cnull%2Cfalse%2Cnull%2C";
      if (!empty($lnk['link'])) //## URL
        $spar.="%5B%7B%2294515327%22%3A%5B%22".$lnk['link']."%22%2C%22".$lnk['img']."%22%5D%7D%5D%2C%5B%5D%2Cnull%2C199%2Cfalse%2Cfalse%2C%22".time().$rnds."%22%5D%7D%5D%5D%5D%5D&at=".$at."&";
      elseif(!empty($lnk['img']) && !empty($imgData)) { //## Image
       $pageIDX = !empty($pageID)?$pageID:$this->pig; //$imgdSize =  strlen(urlencode($imgData));
       $iflds = '{"protocolVersion":"0.8","createSessionRequest":{"fields":[{"external":{"name":"file","filename":"'.$remImgURLFilename.'","put":{},"size":'.$imgdSize.'}},{"inlined":{"name":"batchid","content":"'.time().'97","contentType":"text/plain"}},{"inlined":{"name":"client","content":"google-plus","contentType":"text/plain"}},{"inlined":{"name":"disable_asbe_notification","content":"true","contentType":"text/plain"}},{"inlined":{"name":"effective_id","content":"'.$pageIDX.'","contentType":"text/plain"}},{"inlined":{"name":"owner_name","content":"'.$pageIDX.'","contentType":"text/plain"}},{"inlined":{"name":"album_mode","content":"temporary","contentType":"text/plain"}}]}}';               
       $hdrsArr = $this->headers('', 'https://plus.google.com', 'POST', true); $hdrsArr['X-GUploader-Client-Info']='mechanism=scotty xhr resumable; clientVersion=58505203';  $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $iflds, $this->proxy);
       $imgReqCnt = nxs_remote_post('https://plus.google.com/_/upload/photos/resumable?authuser=0', $advSet); if (is_nxs_error($imgReqCnt)) {  $badOut = print_r($imgReqCnt, true)." - ERROR IMG"; return $badOut; } 
       $gUplURL = str_replace('\u0026', '&', CutFromTo($imgReqCnt['body'], 'putInfo":{"url":"', '"'));  $gUplID = CutFromTo($imgReqCnt['body'], 'upload_id":"', '"'); 
       $hdrsArr = $this->headers('https://plus.google.com/', 'https://plus.google.com', 'PUT'); $hdrsArr['X-Goog-Upload-Offset']='0';  $hdrsArr['X-Goog-Upload-Command']='upload, finalize';  $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $imgData, $this->proxy);       
       $rep = nxs_remote_post($gUplURL, $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR IMG Upl (Upl URL: ".$gUplURL.", IMG URL: ".urldecode($lnk['img']).", FileName: ".$remImgURLFilename.", FIlesize: ".$imgdSize.")"; return $badOut; }        
       $imgUplCnt = json_decode($rep['body'], true);   if (empty($imgUplCnt)) return "Can't upload image: ".$remImgURL."  |  ".print_r($rep, true); // prr($imgUplCnt); 
       if (is_array($imgUplCnt) && isset($imgUplCnt['errorMessage']) && is_array($imgUplCnt['errorMessage']) ) return "Error *NXS Upload* : ".print_r($imgUplCnt['errorMessage'], true);     
       $infoArray = $imgUplCnt['sessionStatus']['additionalInfo']['uploader_service.GoogleRupioAdditionalInfo']['completionInfo']['customerSpecificInfo'];     
       $albumID = $infoArray['albumid']; $photoid =  $infoArray['photoid']; $mk =  urlencode($infoArray['photoMediaKey']); $imgUrl = urlencode($infoArray['url']); $imgTitie = $infoArray['title'];          
       $imgUrlX = str_ireplace('https:', '', $infoArray['url']); $imgUrlX = str_ireplace('//lh4.', '//lh3.', $imgUrlX); $imgUrlX = urlencode(str_ireplace('http:', '', $imgUrlX));
       $width = $infoArray['width']; $height = $infoArray['height']; $userID = $infoArray['username'];      
       $intID = $infoArray['albumPageUrl'];  $intID = str_replace('https://picasaweb.google.com/','', $intID);  $intID = str_replace($userID,'', $intID); $intID = str_replace('/','', $intID); $tmm = time();              
       $spar.="%5B%7B%22".$gNum."%22%3A%5B%5B%5B%22".$mk."%22%2C%22".$imgUrl."%22%2C".$width."%2C".$height."%5D%5D%5D%7D%5D%2C%5B%5D%2Cnull%2C199%2Cfalse%2Cfalse%2C%22".$tmm.'666'.$rnds."%22%5D%7D%5D%5D%5D%5D&at=".$at."&";
      } else //## Just text
       $spar.="null%2C%5B%5D%2Cnull%2C199%2Cfalse%2Cfalse%2C%22".time().$rnds."%22%5D%7D%5D%5D%5D%5D&at=".$at."&";
      $spar = str_ireplace('+','%20',$spar); $spar = str_ireplace(':','%3A',$spar);  $hdrsArr = $this->headers($refPage, 'https://plus.google.com', 'POST'); $hdrsArr['X-Same-Domain']='1'; $hdrsArr['X-Client-Data']='CKC1yQEIhbbJAQiltskBCPyYygE=';
      //$ckt = $ck; $ck = array(); $no = array("LSID", "ACCOUNT_CHOOSER", "GoogleAccountsLocale_session", "GAPS", "GALX"); foreach ($ckt as $c) {if (!in_array($c->name, $no)) $ck[]=$c;}    
      $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $spar, $this->proxy); $rep = nxs_remote_post($gpp, $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR POST"; return $badOut; }  $contents = $rep['body']; 
      //prr($gpp); prr($spar); prr(urldecode($spar));  prr($advSet);    prr($rep);   
      if ($rep['response']['code']=='403') return "Error: You are not authorized to publish to this page. Are you sure this is even a page? (".$pageID.")";
      if ($rep['response']['code']=='404') return "Error: Page you are posting is not found.<br/><br/> If you have entered your page ID as 117008619877691455570/117008619877691455570, please remove the second copy. It should be one number only - 117008619877691455570";
      if ($rep['response']['code']=='400') return "Error (400): Something is wrong, please contact support";
      if ($rep['response']['code']=='500' && stripos($rep['body'], 'RpcClientException')!==false) return "Error (500): Google Server is overloaded or temporary out of service. Message: ".CutFromTo($rep['body'],'RpcClientException',']');
      if ($rep['response']['code']=='500') return "Error (500): Something is wrong, please contact support";
      if ($rep['response']['code']=='200') { $ret = $rep['body']; if (stripos($ret,'"https://plus.google.com/')!==false)  $ret = CutFromTo($contents, '"https://plus.google.com/', '",'); $this->ck = $ck;
        return array('isPosted'=>'1', 'postID'=>$ret, 'postURL'=>'https://plus.google.com/'.$ret, 'pDate'=>date('Y-m-d H:i:s'));
      } return print_r($contents, true);   
    }     
    function postBG($blogID, $title, $msg, $tags=''){ $sslverify = false; $rnds = rndString(35); $blogID = trim($blogID); $ck = $this->ck; 
      $gpp = "https://www.blogger.com/blogger.g?blogID=".$blogID; $refPage = "https://www.blogger.com/home";
      $hdrsArr = $this->headers($refPage); $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get($gpp, $advSet); //prr($ck); prr($rep);// die();
      if (is_nxs_error($rep)) return false; /*if (!empty($rep['cookies'])) $ck = $rep['cookies']; */ $contents = $rep['body']; if ( stripos($contents, 'Error 404')!==false) return "Error: Invalid Blog ID - Blog with ID ".$blogID." Not Found";
      $jjs = CutFromTo($contents, 'BloggerClientFlags=','_layoutOnLoadHandler'); $j69 = ''; // prr($jjs); //  prr($contents); echo "\r\n"; echo "\r\n";    
      for ($i = 54; $i <= 169; $i++) { if ($j69=='' && strpos($jjs, $i.':"')!==false){ $j69 = CutFromTo($jjs, $i.':"','"'); 
        if (strpos($j69, ':')===false || (strpos($j69, '/')!==false) || (strpos($j69, ' ')!==false) || (strpos($j69, '\\')!==false)) $j69 = '';}
      } $gpp = "https://www.blogger.com/blogger_rpc?blogID=".$blogID; $refPage = "https://www.blogger.com/blogger.g?blogID=".$blogID;  if (empty($j69)) return "Error: Code J69. Please contact support"; 
      $spar = '{"method":"editPost","params":{"1":1,"2":"","3":"","5":0,"6":0,"7":1,"8":3,"9":0,"10":2,"11":1,"13":0,"14":{"6":""},"15":"en","16":0,"17":{"1":'.date("Y").',"2":'.date("n").',"3":'.date("j").',"4":'.date("G").',"5":'.date("i").'},"20":0,"21":"","22":{"1":1,"2":{"1":0,"2":0,"3":0,"4":0,"5":0,"6":0,"7":0,"8":0,"9":0,"10":"0"}},"23":1},"xsrf":"'.$j69.'"}';      
      $hdrsArr = $this->headers($refPage, 'https://www.blogger.com', 'JS', false); 
      $hdrsArr['X-GWT-Module-Base']='https://www.blogger.com/static/v1/gwt/'; $hdrsArr['X-GWT-Permutation']='906B796BACD31B64BA497BEE3824B344';      
      $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $spar, $this->proxy);$rep = nxs_remote_post($gpp, $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR BG"; return $badOut; }  $contents = $rep['body']; //  prr($rep);   
      $newpostID = CutFromTo($contents, '"result":[null,"', '"');  
      if ($tags!='') $pTags = '["'.$tags.'"]'; else $pTags = ''; $pTags = str_replace('!','',$pTags); $pTags = str_replace('.','',$pTags);
      if (class_exists('DOMDocument')) { $doc = new DOMDocument();  @$doc->loadXML("<QAZX>".$msg."</QAZX>"); $styles = $doc->getElementsByTagName('style');
        if ($styles->length>0) {  foreach ($styles as $style)  $style->nodeValue = str_ireplace("<br/>", "", $style->nodeValue);
          $msg = $doc->saveXML($doc->documentElement, LIBXML_NOEMPTYTAG); $msg = str_ireplace("<QAZX>", "", str_ireplace("</QAZX>", "", $msg)); 
        }
      } $msg = str_replace("'",'"',$msg); $msg = addslashes($msg); $msg = str_replace("\r\n","\n",$msg); $msg = str_replace("\n\r","\n",$msg); $msg = str_replace("\r","\n",$msg); $msg = str_replace("\n",'\n',$msg);  
      $title = strip_tags($title); $title = str_replace("'",'"',$title); $title = addslashes($title); $title = str_replace("\r\n","\n",$title); 
      $title = str_replace("\n\r","\n",$title); $title = str_replace("\r","\n",$title); $title = str_replace("\n",'\n',$title); //echo "~~~~~";  prr($title);
      
      $spar = '{"method":"editPost","params":{"1":1,"2":"'.$title.'","3":"'.$msg.'","4":"'.$newpostID.'","5":0,"6":0,"7":1,"8":3,"9":0,"10":2,"11":2,'.($pTags!=''?'"12":'.$pTags.',':'').'"13":0,"14":{},"15":"en","16":1,"17":{"1":'.date("Y").',"2":'.date("n").',"3":'.date("j").',"4":'.date("G").',"5":'.date("i").'},"20":0,"21":"","22":{"1":1,"2":{"1":0,"2":0,"3":0,"4":0,"5":0,"6":0,"7":0,"8":0,"9":0,"10":"0"}},"23":3,"26":"","27":1,"28":0},"xsrf":"'.$j69.'"}';      
      $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $spar, $this->proxy); $rep = nxs_remote_post($gpp, $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR BG2"; return $badOut; }  $contents = $rep['body'];
      $retJ = json_decode($contents, true); if (is_array($retJ) && !empty($retJ['result']) && is_array($retJ['result']) ) $postID = $retJ['result'][6]; else $postID = '';
      if ( stripos($contents, '"error":')!==false) { return "Error: ".print_r($contents, true); }
      if ($rep['response']['code']=='200') return array('isPosted'=>'1', 'postID'=>$postID, 'postURL'=>$postID, 'pDate'=>date('Y-m-d H:i:s'), 'ck'=>$ck); else return print_r($contents, true);        
    }    
    function postYT($msg, $ytUrl, $vURL = '', $ytGPPageID='') { $ck = $this->ck; $sslverify = false; 
      $ytUrl = str_ireplace('/feed','',$ytUrl); if (substr($ytUrl, -1)=='/') $ytUrl = substr($ytUrl, 0, -1); $ytUrl .= '/feed?disable_polymer=true'; $hdrsArr = $this->headers('http://www.youtube.com/');
      if ($ytGPPageID!=''){ $pgURL = 'https://www.youtube.com/signin?authuser=0&action_handle_signin=true&pageid='.$ytGPPageID;      if ($this->debug) echo "[YT] G SW to page: ".$ytGPPageID."<br/>\r\n";
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy);$rep = nxs_remote_get($pgURL, $advSet); if (is_nxs_error($rep)) return "ERROR: ".print_r($rep, true);
        if (!empty($rep['cookies'])) foreach ($rep['cookies'] as $ccN) { $fdn = false; foreach ($ck as $ci=>$cc) if ($ccN->name == $cc->name) { $fdn = true; $ck[$ci] = $ccN;  } if (!$fdn) $ck[] = $ccN; }               
      } $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get($ytUrl, $advSet); if (is_nxs_error($rep)) return "ERROR: ".print_r($rep, true);
      //## Merge CK
      if (!empty($rep['cookies'])) foreach ($rep['cookies'] as $ccN) { $fdn = false; foreach ($ck as $ci=>$cc) if ($ccN->name == $cc->name) { $fdn = true; $ck[$ci] = $ccN;  } if (!$fdn) $ck[] = $ccN; }      
      $this->chckForCpt($rep['body'], $ck); // prr($rep);
      $contents = $rep['body']; $gpPageMsg = "Either BAD YouTube USER/PASS or you are trying to post from the wrong account/page. Make sure you have Google+ page ID if your YouTube account belongs to the page.";      
      $actFormCode='channel_ajax'; 
      if (stripos($contents,'action="/channels_feed_ajax?')!==false) $actFormCode='channels_feed_ajax'; elseif (stripos($contents,'action="/c4_feed_ajax?')!==false)$actFormCode = 'c4_feed_ajax';
      if (stripos($contents, 'action="/'.$actFormCode.'?')) $frmData = CutFromTo($contents, 'action="/'.$actFormCode.'?', '</form>'); else { 
        if (stripos($contents, 'property="og:url"')) {  $ytUrl = CutFromTo($contents, 'property="og:url" content="', '"').'/feed'; 
          $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get($ytUrl, $advSet); 
          if (is_nxs_error($rep)) return "ERROR: ".print_r($rep, true); if (!empty($rep['cookies'])) $ck = $rep['cookies'];  $contents = $rep['body'];        
          if (stripos($contents, 'action="/'.$actFormCode.'?')) $frmData = CutFromTo($contents, 'action="/'.$actFormCode.'?', '</form>'); else return 'OG - Form not found. - '. $gpPageMsg;
        } else { $eMsg = "No Form/No OG - ". $gpPageMsg; return $eMsg; }
      } $md = array(); $flds = array(); if (!empty($vURL) && stripos($vURL, 'http')===false && strlen($vURL)!=11) $vURL = ''; 
      if ($vURL!='' && stripos($vURL, 'http')===false) $vURL = 'https://www.youtube.com/watch?v='.$vURL; $msg = strip_tags($msg); $msg = nsTrnc($msg, 500);
      while (stripos($frmData, '"hidden"')!==false){$frmData = substr($frmData, stripos($frmData, '"hidden"')+8); $name = trim(CutFromTo($frmData,'name="', '"'));
        if (!in_array($name, $md)) {$md[] = $name; $val = trim(CutFromTo($frmData,'value="', '"')); $flds[$name]= $val;}
      } $flds['message'] = $msg; $flds['video_url'] = $vURL; $flds['session_token'] = trim(CutFromTo($contents,'XSRF_TOKEN\': "', '"'));   
      $flds['params'] = 'CAE%3D'; $flds['video_id'] = ''; $flds['playlist_id'] = ''; //prr($flds);
      $ytGPPageID = 'https://www.youtube.com/channel/'.$ytGPPageID; $hdrsArr = $this->headers($ytGPPageID, 'https://www.youtube.com/', 'POST', false); 
      $hdrsArr['X-YouTube-Page-CL'] = '67741289'; $hdrsArr['X-YouTube-Page-Timestamp'] = date("D M j H:i:s Y", time()-54000)." (".time().")"; //'Thu May 22 00:31:51 2014 (1400743911)';      
      $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $flds, $this->proxy); $rep = nxs_remote_post('https://www.youtube.com/'.$actFormCode.'?action_create_channel_post=1', $advSet); //prr($rep); prr($advSet); 
      if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR YT"; return $badOut; }  $contents = $rep['body']; //prr($contents); 
      if ($rep['response']['code']=='200' && ( $contents == '{"code": "SUCCESS"}' || stripos($contents,'"feed_entry_html":')!==false )) return array("isPosted"=>"1", "postID"=>'', 'postURL'=>$ytUrl, 'pDate'=>date('Y-m-d H:i:s'), 'ck'=>$ck); else return $rep['response']['code']."|".$contents;     
    }              
}}
//================================Pinterest=====================================
if (!class_exists('nxsAPI_PN')){class nxsAPI_PN{ var $ck = array(); var $tk=''; var $boards = ''; var $apVer=''; var $u=''; var $debug = false; var $loc = ''; var $proxy = array();
    function headers($ref, $org='', $type='GET', $aj=false){  $hdrsArr = array(); 
      $hdrsArr['Cache-Control']='max-age=0'; $hdrsArr['Connection']='keep-alive'; $hdrsArr['Upgrade-Insecure-Requests']='1'; $hdrsArr['Referer']=$ref;
      $hdrsArr['User-Agent']='Mozilla/5.0 (Windows NT 6.1; WOW64; rv:49.0) Gecko/20100101 Firefox/49.0'; 
      if($type=='JSON') $hdrsArr['Content-Type']='application/json;charset=UTF-8'; elseif($type=='POST') $hdrsArr['Content-Type']='application/x-www-form-urlencoded';
      if($aj===true) $hdrsArr['X-Requested-With']='XMLHttpRequest';  if ($org!='') $hdrsArr['Origin']=$org; 
      if ($type=='GET') $hdrsArr['Accept']='text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'; else $hdrsArr['Accept']='*/*';
      if (function_exists('gzdeflate')) $hdrsArr['Accept-Encoding']='gzip, deflate'; 
      $hdrsArr['Accept-Language']='en-US,en;q=0.8'; return $hdrsArr;         
    }        
    function check($u=''){ $ck = $this->ck; if (!empty($ck) && is_array($ck)) { if (empty($this->loc)) $this->getLoc(); $hdrsArr = $this->headers($this->loc.'settings/'); if ($this->debug) echo "[PN] Checking....;<br/>\r\n";
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get($this->loc.'settings/', $advSet);                 
        if (is_nxs_error($rep)) return false; $ck = $rep['cookies']; $contents = $rep['body'];// if ($this->debug) prr($contents);
        $ret = stripos($contents, '"username":"')!==false; $usre = CutFromTo($contents, '"email":"', '"'); $usr = CutFromTo($contents, '"username":"', '"'); if ($ret & $this->debug) echo "[PN] Logged as:".$usr." (".$usre.")<br/>\r\n"; 
        $apVer = trim(CutFromTo($contents,'"app_version": "', '"'));  $this->apVer = $apVer; 
        if (empty($u) || $u==$usr || $u==$usre) return $ret; else return false;
      } else return false;
    }
    function connect($u,$p){ $badOut = 'Error: ';
      //## Check if alrady IN
      if (!$this->check($u)){ if ($this->debug) echo "[PN] NO Saved Data; Logging in...<br/>\r\n"; if (empty($this->loc)) { $er = $this->getLoc(); if (!empty($er)) return $er; }
        $hdrsArr = $this->headers($this->loc.'login/'); $advSet = nxs_mkRemOptsArr($hdrsArr, '', '', $this->proxy); $rep = nxs_remote_get($this->loc.'login/', $advSet);
        if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR -01-"; return $badOut; } $ck = $rep['cookies']; $contents = $rep['body']; $apVer = trim(CutFromTo($contents,'"app_version": "', '"')); $p = addslashes(stripslashes($p));
        $flds = array('data'=>'{"options":{"username_or_email":"'.$u.'","password":"'.$p.'"},"context":{"app_version":"7b9caab"}}', 'source_url'=>'/login/', 'module_path'=>'App()>LoginPage()>Login()>Button(class_name=primary, text=Log in, type=submit, tagName=button, size=large)'); foreach ($ck as $c) if ($c->name=='csrftoken') $xftkn = $c->value;
        //## ACTUAL LOGIN 
        $hdrsArr = $this->headers($this->loc.'login/', $this->loc, 'POST', true); $hdrsArr['X-NEW-APP']='1'; $hdrsArr['X-APP-VERSION']=$apVer; $hdrsArr['X-CSRFToken']=$xftkn;                
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $flds, $this->proxy); $rep = nxs_remote_post($this->loc.'resource/UserSessionResource/create/', $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR -02-"; return $badOut; } 
        if (!empty($rep['headers']['location'])) { $loc = CutFromTo($rep['headers']['location'], 'https://','.pinterest');  
          $hdrsArr = $this->headers('https://'.$loc.'.pinterest.com/login/', 'https://'.$loc.'.pinterest.com', 'POST', true); $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $flds, $this->proxy);
          $rep = nxs_remote_post('https://'.$loc.'.pinterest.com/resource/UserSessionResource/create/', $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR -02-"; return $badOut; }  
        } else $loc = 'www';
        if (!empty($rep['body'])) { $contents = $rep['body']; $resp = json_decode($contents, true); } else { $badOut = print_r($rep, true)." - ERROR -03-"; return $badOut; }
          if (is_array($resp) && empty($resp['resource_response']['error'])) { $ck = $rep['cookies'];  foreach ($ck as $ci=>$cc) $ck[$ci]->value = str_replace(' ','+', $cc->value);  
            $hdrsArr = $this->headers('https://'.$loc.'.pinterest.com/login'); $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); 
            $rep=nxs_remote_get('https://'.$loc.'.pinterest.com/', $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR -02.1-"; return $badOut; } 
            if (!empty($rep['cookies'])) foreach ($rep['cookies'] as $ccN) { $fdn = false; foreach ($ck as $ci=>$cc) if ($ccN->name == $cc->name) { $fdn = true; $ck[$ci] = $ccN;  } if (!$fdn) $ck[] = $ccN; }
            foreach ($ck as $ci=>$cc) $ck[$ci]->value = str_replace(' ','+', $cc->value); $this->tk = $xftkn; $this->ck = $ck;  $this->apVer = $apVer;  $this->getLoc();         
            if ($this->debug) echo "[PN] You are IN;<br/>\r\n"; return false; // echo "You are IN";                                       
          } elseif (is_array($resp) && isset($resp['resource_response']['error'])) return "ERROR -04-: ".$resp['resource_response']['error']['http_status']." | ".$resp['resource_response']['error']['message'];
          elseif (stripos($contents, 'CSRF verification failed')!==false) { $retText = trim(str_replace(array("\r\n", "\r", "\n"), " | ", strip_tags(CutFromTo($contents, '</head>', '</body>'))));
            return "CSRF verification failed - Please contact NextScripts Support | Pinterest Message:".$retText;
          } elseif (stripos($contents, 'IP because of suspicious activity')!==false) return 'Pinterest blocked logins from this IP because of suspicious activity'; 
          elseif (stripos($contents, 've detected a bot!')!==false || stripos($contents, 'bot running on your network')!==false) { $ip = stripos($contents, 'ess: <b>')!==false? '('.CutFromTo($contents, 'ess: <b>','<').') ':'';
              return '<br/>Pinterest has your Hosting IP '.$ip.'in the list of potentially suspicious networks and blocked it.<br/><a href="http://nxs.fyi/faq65" target="_blank">Please see FAQ #6.5</a>.'; }
          else return 'Pinterest login failed. Unknown Error. Please contact support.';           
          return 'Pinterest login failed. Unknown Error #2. Please contact support.'; 
      } else { if ($this->debug) echo "[PN] Saved Data is OK;<br/>\r\n"; return false; }
    }    
    function getLoc(){ $ck = $this->ck; $hdrsArr = $this->headers('https://www.pinterest.com/');  
       $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get('https://www.pinterest.com/nxs8067/followers/', $advSet); //prr($rep);
       if (is_nxs_error($rep)) {  $badOut = "GETLOCK ERROR: ".print_r($rep, true)." | <br/> REQ: ".print_r($advSet, true); return $badOut; }
       if ($rep['response']['code']=='200' || $rep['response']['code']=='403') $this->loc = 'https://www.pinterest.com/'; elseif ($rep['response']['code']=='302' && !empty($rep['headers']['location'])) $this->loc = 'https://'.CutFromTo($rep['headers']['location'].'/', "//", '/').'/'; 
    }
    function getBoards($curr='') {  if ($this->debug) echo "[PN] Getting Boards ...<br/>\r\n";   $boards = ''; $ck = $this->ck; $apVer = $this->apVer; $brdsArr = array(); if (empty($this->loc)) { $er = $this->getLoc(); if (!empty($er)) return $er; }
        $noBoardsMsg = '<span style="color:red">No Boards Found. Please login to your pinterest.com account and create at least one</span>';
        $iu = 'http://memory.loc.gov/award/ndfa/ndfahult/c200/c240r.jpg'; $su = '/pin/find/?url='.urlencode($iu); $iuu = urlencode($iu); $hdrsArr = $this->headers($this->loc,'','JSON', true);         
        $hdrsArr['X-NEW-APP']='1'; $hdrsArr['X-APP-VERSION']=$apVer; $hdrsArr['X-Pinterest-AppState']='active'; $hdrsArr['Accept'] = 'application/json, text/javascript, */*; q=0.01';                
        $brdURL = $this->loc.'resource/BoardPickerBoardsResource/get/?source_url=%2Fpin%2Ffind%2F%3Furl%'.$iuu.'&data=%7B%22options%22%3A%7B%22filter%22%3A%22all%22%2C%22field_set_key%22%3A%22board_picker%22%7D%2C%22context%22%3A%7B%7D%7D&module_path=App()%3EImagesFeedPage(resource%3DFindPinImagesResource(url%'.$iuu.'))%3EGrid()%3EGridItems()%3EPinnable()%3EShowModalButton(module%3DPinCreate)';$advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get($brdURL, $advSet);        
        if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR"; return $badOut; } $ck = $rep['cookies']; $contents = $rep['body']; $k = json_decode($contents, true);      //  prr($k);
        if (!empty($k['resource_data_cache']) || !empty($k['resource_response'])) { if (!empty($k['resource_data_cache'])) $brdsA = $k['resource_data_cache']; else {$brdsA = array(); $brdsA[] = $k['resource_response']; }
          foreach ($brdsA as $ab) if (!empty($ab) && !empty($ab['data']['all_boards'])) { $ba = $ab['data']['all_boards']; 
            foreach ($ba as $kh) { $boards .= '<option '.($curr==$kh['id']?'selected="selected"':'').' value="'.$kh['id'].'">'.$kh['name'].'</option>'; $brdsArr[] = array('id'=>$kh['id'], 'n'=>$kh['name']); } $this->boards = $brdsArr; return $boards; 
          } else return $noBoardsMsg;
        } else return 'Can\'t get data, please try again';
    }
    function post($msg, $imgURL, $lnk, $boardID, $title = '', $price='', $via=''){ 
      $tk = $this->tk; $ck = $this->ck; $apVer = $this->apVer; if ($this->debug) echo "[PN] Posting to ...".$boardID."<br/>\r\n";  if (empty($this->loc)) { $er = $this->getLoc(); if (!empty($er)) return $er; }    
      foreach ($ck as $c) if ( is_object($c) && $c->name=='csrftoken') $tk = $c->value; $msg = strip_tags($msg); $msg = substr($msg, 0, 480); $tgs = ''; $this->tk = $tk;
      if ($msg=='') $msg = '&nbsp;'; if (empty($boardID)) return "Board is not set, please retrieve and select a board.";  if (trim($imgURL)=='') return "Image is not Set";   $msg = str_ireplace(array("\r\n", "\n", "\r"), " ", $msg); 
      $msg = strip_tags($msg); if (function_exists('nxs_decodeEntitiesFull')) $msg = nxs_decodeEntitiesFull($msg, ENT_QUOTES); 
      $mgsOut = urlencode($msg); $mgsOut = str_ireplace(array('%28', '%29', '%27', '%21', '%22', '%09'), array("(", ")", "'", "!", "%5C%22", '%5Ct'), $mgsOut);     
      $fldsTxt = 'source_url=%2Fpin%2Ffind%2F%3Furl%3D'.urlencode(urlencode($lnk)).'&data=%7B%22options%22%3A%7B%22board_id%22%3A%22'.$boardID.'%22%2C%22description%22%3A%22'.$mgsOut.'%22%2C%22link%22%3A%22'.urlencode($lnk).'%22%2C%22share_twitter%22%3Afalse%2C%22image_url%22%3A%22'.urlencode($imgURL).'%22%2C%22method%22%3A%22scraped%22%7D%2C%22context%22%3A%7B%7D%7D';
      $hdrsArr = $this->headers($brdURL = $this->loc.'resource/PinResource/create/ ', $brdURL = $this->loc, 'POST', true);       
      $hdrsArr['X-NEW-APP']='1'; $hdrsArr['X-APP-VERSION']=$apVer; $hdrsArr['X-CSRFToken']=$tk; $hdrsArr['X-Pinterest-AppState']='active';  $hdrsArr['Accept'] = 'application/json, text/javascript, */*; q=0.01';            
      $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $fldsTxt, $this->proxy); $rep = nxs_remote_post($brdURL = $this->loc.'resource/PinResource/create/', $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR"; return $badOut; }       
      $contents = $rep['body']; $resp = json_decode($contents, true); //  prr($advSet);  prr($resp);   prr($fldsTxt); // prr($contents);    
      if (is_array($resp)) {
        if (isset($resp['resource_response']) && isset($resp['resource_response']['error']) && $resp['resource_response']['error']!='' ) return 'RSP Error: '.print_r($resp['resource_response']['error'], true); 
        elseif (isset($resp['resource_response']) && isset($resp['resource_response']['data']) && $resp['resource_response']['data']['id']!=''){ // gor JSON
          if (isset($resp['resource_response']) && isset($resp['resource_response']['error']) && $resp['resource_response']['error']!='') return 'RSP Error (No ID): '.print_r($resp['resource_response']['error'], true);
            else { $this->ck = $ck; return array("isPosted"=>"1", "postID"=>$resp['resource_response']['data']['id'], 'pDate'=>date('Y-m-d H:i:s'), "postURL"=>$brdURL = $this->loc.'pin/'.$resp['resource_response']['data']['id']); }
        }    
      }elseif (stripos($contents, 'blocked this')!==false) { $retText = trim(str_replace(array("\r\n", "\r", "\n"), " | ", strip_tags(CutFromTo($contents, '</head>', '</body>'))));
        return "Pinterest ERROR: 'The Source is blocked'. Please see https://support.pinterest.com/entries/21436306-why-is-my-pin-or-site-blocked-for-spam-or-inappropriate-content/ for more info | Pinterest Message:".$retText;
      }  
      elseif (stripos($contents, 'image you tried to pin is too small')!==false) { $retText = trim(str_replace(array("\r\n", "\r", "\n"), " | ", strip_tags(CutFromTo($contents, '</head>', '</body>'))));
        return "Image you tried to pin is too small | Pinterest Message:".$retText;
      }  
      elseif (stripos($contents, 'CSRF verification failed')!==false) { $retText = trim(str_replace(array("\r\n", "\r", "\n"), " | ", strip_tags(CutFromTo($contents, '</head>', '</body>'))));
        return "CSRF verification failed - Please contact NextScripts Support | Pinterest Message:".$retText;
      }
      elseif (stripos($contents, 'Oops')!==false && stripos($contents, '<body>')!==false ) return 'Pinterest ERROR MESSAGE : '.trim(str_replace(array("\r\n", "\r", "\n"), " | ", strip_tags(CutFromTo($contents, '</head>', '</body>'))));
      else return "Somethig is Wrong - Pinterest Returned Error 502";         
    }    
}} 
//================================LinkedIn======================================
if (!class_exists('nxsAPI_LI')){class nxsAPI_LI{ var $ck = array();  var $debug = false; var $proxy = array(); var $sid = '';  var $liUser = array('aid'=>'','pid'=>'','mid'=>'');
    function headers($ref, $org='', $type='GET', $aj=false){  $hdrsArr = array(); 
      $hdrsArr['Cache-Control']='max-age=0'; $hdrsArr['Connection']='keep-alive'; $hdrsArr['Referer']=$ref; $hdrsArr['Upgrade-Insecure-Requests']='1'; 
      $hdrsArr['User-Agent']='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36';           
      //$hdrsArr['User-Agent']='Mozilla/5.0 (Linux; U; Android 4.0.4; en-gb; GT-I9300 Build/IMM76D) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30';
      if($type=='JSON') $hdrsArr['Content-Type']='application/json;charset=UTF-8'; elseif($type=='POST') $hdrsArr['Content-Type']='application/x-www-form-urlencoded';
      if($aj===true) $hdrsArr['X-Requested-With']='XMLHttpRequest';  if ($org!='') $hdrsArr['Origin']=$org; 
      if ($type=='GET') $hdrsArr['Accept']='text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'; else $hdrsArr['Accept']='*/*';
      if (function_exists('gzdeflate')) $hdrsArr['Accept-Encoding']='deflate,sdch'; 
      $hdrsArr['Accept-Language']='en-US,en;q=0.8'; return $hdrsArr;         
    } 
    function setSession(){
      if (!empty($this->sid)) { if (empty($this->ck)) $this->ck = array(); if ($this->debug) echo "[FP] Setting Session...<br/>\r\n"; 
          foreach ($this->ck as $ci=>$cc) if ( $this->ck[$ci]->name=='li_at') unset($this->ck[$ci]); $c = new NXS_Http_Cookie( array('name' => 'li_at', 'value' => $this->sid) ); $this->ck[] = $c; 
      } 
    }   
    function check($u=''){ $this->setSession(); $ck = $this->ck;  if (!empty($ck) && is_array($ck)) { $hdrsArr = $this->headers('https://www.linkedin.com', '','JSON', true); if ($this->debug) echo "[LI] Checking....;<br/>\r\n"; 
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get('https://www.linkedin.com/psettings/email?asJson=true', $advSet);  //prr($advSet);  prr($rep);
        if (is_nxs_error($rep)) return false; $ck = $rep['cookies']; $contents = $rep['body']; $js = json_decode($contents, true); //prr($js);
        if (!empty($js) && is_array($js) && !empty($js['map']) && !empty($js['map']['data'])) { $js = $js['map']['data']; $em = array();  if (!empty($js['email'])) $em[] = $js['email'];
          foreach ($js as $j) if (!empty($j['email'])) $em[] = $j['email']; if (!empty($u)) $isChecked = in_array($u,$em); else $isChecked = !empty($em);
        } else return false;
        //$isChecked = stripos($contents, '<h3 class="member-name">')!==false; 
        if ($isChecked)  $this->ck = nxs_MergeCookieArr($this->ck, $ck);  return $isChecked;
      } else return false;
    }
    function connect($u,$p){ $badOut = 'Connect Error: '; 
        //## Check if alrady IN
        if (!$this->check($u)){ if ($this->debug) echo "[LI] NO Saved Data;<br/>\r\n";  
        $hdrsArr = $this->headers('https://www.linkedin.com'); $advSet = nxs_mkRemOptsArr($hdrsArr, '', '', $this->proxy); $rep = nxs_remote_get('https://www.linkedin.com/uas/login?goback=&trk=hb_signin', $advSet); // prr($rep);
        if (is_nxs_error($rep)) {  $badOut = "AUTH ERROR #1". print_r($rep, true); return $badOut; } $ck = nxsClnCookies($rep['cookies']); $contents = $rep['body']; if (!empty($this->proxy)) { $prx = explode(':',$this->proxy['proxy']); $this->proxy = $prx; }
        //## GET HIDDEN FIELDS
        $md = array(); $flds  = array(); $treeID = trim(CutFromTo($contents,'name="treeID" content="', '"'));
        while (stripos($contents, '<input')!==false){ $inpField = trim(CutFromTo($contents,'<input', '>')); $name = trim(CutFromTo($inpField,'name="', '"'));
          if ( stripos($inpField, '"hidden"')!==false && $name!='' && !in_array($name, $md)) { $md[] = $name; $val = trim(CutFromTo($inpField,'value="', '"')); $flds[$name]= $val; }
          $contents = substr($contents, stripos($contents, '<input')+8);
        } $flds['session_key'] = $u; $flds['session_password'] = $p;  $flds['signin'] = 'Sign%20In'; 
        //## ACTUAL LOGIN         
        $hdrsArr = $this->headers('https://www.linkedin.com/', 'https://www.linkedin.com', 'POST', true); $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $flds, $this->proxy);// prr($advSet);
        $rep = nxs_remote_post('https://www.linkedin.com/uas/login-submit', $advSet);if (is_nxs_error($rep)) {  $badOut = "AUTH ERROR #2". print_r($rep, true); return $badOut; }  $ck = nxsClnCookies(nxs_MergeCookieArr($ck, $rep['cookies']));         
        if ($rep['response']['code']=='302' && !empty($rep['headers']['location'])) { $hdrsArr = $this->headers('https://www.linkedin.com/'); 
          $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy);  $rep = nxs_remote_get($rep['headers']['location'], $advSet);  
          if (is_nxs_error($rep)) return 'ERROR (Login R2.) '.print_r($rep, true);  if ($rep['response']['code']=='302' && !empty($rep['headers']['location'])) return 'ERROR (Login R3) R to: '.print_r($rep['headers']['location'], true);
          if (stripos($rep['body'], 'profile-edit-ext')!==false) { if ($this->debug) echo "[LI] Login was OK;<br/>\r\n"; $this->ck = $ck; return false; }                
        } //prr($rep);
        if ($rep['response']['code']=='200') { $content = $rep['body']; //prr($content); die();
           if (stripos($content, 'session_password-login-error')!==false) { return "Hmm, that's not the right password. Please try again.";}                      
           if (!empty($rep['cookies'])) foreach ($rep['cookies'] as $ccN) { $fdn = false; foreach ($ck as $ci=>$cc) if ($ccN->name == $cc->name) { $fdn = true; $ck[$ci] = $ccN;  } if (!$fdn) $ck[] = $ccN; } 
           if (stripos($content, '"status":"ok"')!==false) { if (stripos($content, 'redirectUrl')!==false) { if ($this->debug) echo "[LI] Login REDIR;<br/>\r\n";
             $content = str_ireplace('/uas/','https://www.linkedin.com/uas/',$content); $rJson = json_decode($content, true);
             if (!empty($rep['cookies'])) foreach ($rep['cookies'] as $ccN) { $fdn = false; foreach ($ck as $ci=>$cc) if ($ccN->name == $cc->name) { $fdn = true; $ck[$ci] = $ccN;  } if (!$fdn) $ck[] = $ccN; }  
             $hdrsArr = $this->headers('https://www.linkedin.com/uas/login-submit'); $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy,0); $rep = nxs_remote_get($rJson['redirectUrl'], $advSet); //prr($rep);  prr($advSet);  $content = $rep['body']; 
           } else { if ($this->debug) echo "[LI] Login was OK;<br/>\r\n"; $this->ck = $ck; return false; }}              
           if (stripos($content, 'ou have exceeded the maximum number of code requests')!==false) { return "You have exceeded the maximum number of code requests. Please try again later.";}                                 
           if (stripos($content, 'we need you to reset your password as a security precaution')!==false) { return "Login Error - LINKEDIN Message:  Sorry, we need you to reset your password as a security precaution. We have resent the email. Please check your email now.";}           
           if (stripos($content, 'play.checkpoint.login.control')!==false) { return "Login Error - No Access(RCNR). Please try <a href='http://nxs.fyi/liac'>Alternative LinkedIn Configuration - http://nxs.fyi/liac</a>";}           
           if (stripos($content, '"submitRequired":true')!==false) { unset($hdrsArr['X-IsAJAXForm']);  unset($hdrsArr['X-LinkedIn-traceDataContext']); unset($hdrsArr['X-Requested-With']);
             $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $flds, $this->proxy); $rep = nxs_remote_post('https://www.linkedin.com/uas/login-submit', $advSet); if (is_nxs_error($rep)) {  $badOut = 'ERR 2:'.print_r($rep, true); return $badOut; }  $content = $rep['body'];
           }           
           if ( stripos($content, 'name="PinVerificationForm_pinParam"')!==false) { $fa = CutFromTo($content,'action="','"'); //## Code             
               if ( stripos($content, '<div id="uas-consumer-two-step-verification" class="two-step-verification">')!==false) {
                 $text = CutFromTo($content, '<div id="uas-consumer-two-step-verification" class="two-step-verification">', '<script id="').'</li></ul></form></div></div>';               
                 $formcode = '<form '.CutFromTo($content, '<div id="uas-consumer-two-step-verification" class="two-step-verification">', '</form>');  
               } else { $text = CutFromTo($content, '<div id="uas-consumer-ato-pin-challenge" class="two-step-verification">', '<script id="').'</li></ul></form></div></div>';               
                 $formcode = '<form '.CutFromTo($content, '<div id="uas-consumer-ato-pin-challenge" class="two-step-verification">', '</form>');  
               }
               while (stripos($formcode, '"hidden"')!==false){$formcode = substr($formcode, stripos($formcode, '"hidden"')+8); $name = trim(CutFromTo($formcode,'name="', '"'));
                 if (!in_array($name, $md)) { $md[] = $name; $val = trim(CutFromTo($formcode,'value="', '"')); $flds[$name]= $val; }
               } $flds['session_key'] = $u; $flds['session_password'] = $p;  $flds['signin'] = 'Sign%20In'; 
               $ser = array(); $ser['c'] = $ck; $ser['f'] = $flds;  $ser['fa'] = $fa;  $seForDB = serialize($ser); return array('out' => $text, 'ser'=>$seForDB);
           }                  
           if (stripos($content, 'captcha recaptcha')!==false) {//## Captcha
             $ca = nxs_remote_get('https://www.google.com/recaptcha/api/noscript?k=6LcnacMSAAAAADoIuYvLUHSNLXdgUcq-jjqjBo5n'); 
             if (is_nxs_error($ca)) {  $badOut = print_r($ca, true)." - [captcha] ERROR"; return $badOut; } $img = CutFromTo($ca['body'], 'src="image?c=', '"'); 
             $formcode = '<form '.CutFromTo($content, '<form action="https://www.linkedin.com/uas/captcha-submit" ', '</form>');  $formcode = str_ireplace('</iframe>', '', $formcode); 
             $formcode = str_ireplace('<iframe src="https://www.google.com/recaptcha/api/noscript?k=6LcnacMSAAAAADoIuYvLUHSNLXdgUcq-jjqjBo5n" height="300" width="500" frameborder="0">', $ca['body'], $formcode);
             return array('cimg' => $img, 'ck'=>$ck, 'formcode'=>$formcode);
           }
           if (stripos($content, '/uas/consumer-captcha-v2')!==false) {//## Captcha V2             
             $frmm = 'Unfortunately your server IP is blacklisted by LinkedIn due to the previous abuse. <br/><br/>LinkedIn is asking you to enter captcha to unlock your IP.<br/><br/>Here is what you can do:<br/>1. Get a working proxy and configure it in both your browser and plugin.';
             $frmm .= '2. Login to LinkedIn from your browser. Solve captcha (if asked)<br/> 3. Make a test post from the plguin.<br/><br/> 4. Remove proxy and post a test again. <br/>';
             echo $frmm; die();
           }
           if (stripos($content, '"status":"fail"')!==false) { if ($this->debug) echo "[LI] Login failed;<br/>\r\n";
             $content = str_ireplace('href="/uas/','href="https://www.linkedin.com/uas/',$content); $rJson = json_decode($content, true); $badOut = "LOGIN ERROR: ".print_r($rJson, true); 
             if (stripos($content, 'There were one or more errors')!==false) $badOut .= "\r\n<br/>Error Message:  Hmm, that's not the right password. Please try again."; return $badOut; 
           }         
           if (stripos($content, 'textarea name="postText"')!==false || stripos($content, 'id="sharebox-container"')!==false || stripos($content, 'class="initial-load-animation"')!==false) { if ($this->debug) echo "[LI] Login OK; Got Form; <br/>\r\n"; $this->ck = $ck; return false;}
        } return $badOut." LI MSG:".print_r($rep, true);
      } else { if ($this->debug) echo "[LI] Saved Data is OK;<br/>\r\n"; return false; }
    }
    function getPgsList($pgID){ $ck = $this->ck; $pgs = ''; if (!empty($ck) && is_array($ck)) { $hdrsArr = $this->headers('https://www.linkedin.com'); if ($this->debug) echo "[LI] PG List....;<br/>\r\n"; 
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get('https://www.linkedin.com/jobs/career-interests/', $advSet); // prr($advSet);  prr($rep);
        if (is_nxs_error($rep)) return false; $ck = $rep['cookies']; $contents = $rep['body']; if (stripos($contents, '&quot;companies&quot;')!==false) { 
          $ct = str_replace('&quot;','"','{&quot;data&quot;:{&quot;companies&quot;'.CutFromTo($contents, '&quot;companies&quot;','}]}').'}]}'); $ct = json_decode($ct, true);// prr($ct);
          if (!empty($ct) && is_array($ct) && !empty($ct['included'])) {  $ct = $ct['included'];
            foreach ($ct as $c) { $cid = explode(':',$c['objectUrn']); $cid = $cid[3];
               if (!empty($cid)) $pgs .= '<option class="nxsBlue" '.(($pgID==$cid)?'selected="selected"':'').' value="'.$cid.'" data-val="'.$cid.'">'.$c['name'].' ('.$cid.')</option>'; 
            } 
          } //prr($ct); die();    
        } 
    } return $pgs; }
    function getGrpList($pgID){ $ck = $this->ck; $pgs = ''; if (!empty($ck) && is_array($ck)) { foreach ($ck as $ci=>$cc) { if($cc->name =='JSESSIONID') $csrft = str_replace('"','',$cc->value);}
        $hdrsArr = $this->headers('https://www.linkedin.com', 'https://www.linkedin.com'); $hdrsArr['Csrf-Token'] = $csrft;         
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get('https://www.linkedin.com/groups/my-groups', $advSet);
        if (is_nxs_error($rep)) return false; $contents = $rep['body']; if (stripos($contents, '"id":"')!==false) { $uid = CutFromTo($contents, '"id":"', '"'); }        
        $hdrsArr = $this->headers('https://www.linkedin.com', 'https://www.linkedin.com', 'GET', true); $hdrsArr['Csrf-Token'] = $csrft;         
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get('https://www.linkedin.com/communities-api/v1/communities/memberships/'.$uid.'?projection=FULL&sortBy=RECENTLY_JOINED&count=500', $advSet); 
        if (is_nxs_error($rep)) return false; $contents = $rep['body']; if (stripos($contents, '"miniMembership":')!==false) { $ct = json_decode($contents, true); 
          if (!empty($ct) && is_array($ct) && !empty($ct['data'])) $ct = $ct['data']; 
          foreach ($ct as $c) if (!empty($c['id'])||!empty($c['group']['id'])) { $pgs .= '<option class="nxsGreen" '.(($pgID==$c['group']['id'] || $pgID==$c['id'])?'selected="selected"':'').' value="'.$c['group']['id'].'" data-val="'.$c['id'].'">'.$c['group']['mini']['name'].' ('.$c['group']['id'].')</option>'; }
        } 
    } return $pgs; }
    function postToPulse($msg, $title, $html, $imgURL){ global $nxs_plurl; $ck = $this->ck;  foreach ($ck as $ci=>$cc) { if($cc->name =='JSESSIONID') $csrft = str_replace('"','',$cc->value);} //prr($csrft);
        $hdrsArr = $this->headers('https://www.linkedin.com', 'https://www.linkedin.com', 'JSON', true); $hdrsArr['Csrf-Token'] = $csrft; $hdrsArr['Accept'] = 'application/json, text/javascript, */*; q=0.01';
        $flds = '{"customPublishMessage":{"text":""},"authors":["urn:li:member:333698448"],"state":"DRAFT","title":"Post me","contentHtml":""}';
        $pURL = 'https://www.linkedin.com/voyager/api/publishing/normFirstPartyArticle'; $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $flds, $this->proxy); $rep = nxs_remote_post($pURL, $advSet); //prr($rep);
        if (is_nxs_error($rep)) {  $badOut = 'Pulse Error: '.print_r($rep, true); return $badOut; } elseif ($rep['response']['code']=='201' && !empty($rep['headers']['location'])) $pID = substr(strrchr($rep['headers']['location'], "/"), 1);                 
        $dvdr = 'WebKitFormBoundaryvdfQslA1ksAfZbR1'; $ctd = 'Content-Disposition: '; $hdrsArr['User-Agent']='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36'; 
        $gURL = 'https://www.linkedin.com/voyager/api/fileUploadToken?type=PUBLISHING_IMAGE'; $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get($gURL, $advSet); $uplToken = json_decode($rep['body'], true); $uplToken = $uplToken['uploadToken'];        
        $txt ='------'.$dvdr."\r\n".$ctd.'form-data; name="sign_response"'."\r\n\r\n".'true'."\r\n".'------'.$dvdr."\r\n".$ctd.'form-data; name="persist"'."\r\n\r\n".'true'."\r\n".'------'.$dvdr."\r\n".$ctd.'form-data; name="callback"'."\r\n\r\n".'uploadCallback1431645833521'."\r\n".'------'.$dvdr."\r\n".$ctd.'form-data; name="csrfToken"'."\r\n\r\n".$csrft."\r\n".'------'.$dvdr."\r\n".$ctd.'form-data; name="upload_info"'."\r\n\r\n".$uplToken."\r\n";
        $advSet = nxs_mkRemOptsArr($hdrsArr, '', '', $this->proxy); list($width, $height) = getimagesize($imgURL); $imgData = nxs_remote_get($imgURL, $advSet); //prr($lnkArr['img']);
        if(is_nxs_error($imgData) || empty($imgData['body']) || (!empty($imgData['headers']['content-length']) && (int)$imgData['headers']['content-length']<200)) { $options['attchImg'] = 0; 
          $badOut[] = 'Image Error: Could not get image ('.$lnkArr['img'].'), will post without it - Error:'.print_r($imgData, true);            
        } else $imgData = $imgData['body'];          
        $params  = $txt."------".$dvdr."\r\n".$ctd."form-data; name=\"file\"; filename=\"image.jpg\"\r\nContent-Type: image/jpg\r\n\r\n".$imgData."\r\n------".$dvdr."--";                    
        $hdrsArr = $this->headers('https://www.linkedin.com/post/new?trk=hp-share-poncho-pencil', 'http://www.linkedin.com', 'POST');  unset($hdrsArr['Content-Type']);  $hdrsArr['Content-Type']='multipart/form-data; boundary=----'.$dvdr; 
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $params, $this->proxy); $rep = nxs_remote_post('https://www.linkedin.com/mupld/megaImageUpload', $advSet); if (is_nxs_error($rep)) {  $badOut[] = 'Image Error: '.print_r($rep, true); } //prr($rep, 'IMG1');
        $ImgCode1 = json_decode($rep['body'], true); $ImgCode1 = $ImgCode1['value']; 
        
        $hdrsArr = $this->headers('https://www.linkedin.com/post/new?trk=hp-share-poncho-pencil', 'https://www.linkedin.com', 'POST', true); $hdrsArr['Csrf-Token'] = $csrft; $hdrsArr['Accept'] = '*/*';
        $flds = 'mid='.urlencode($ImgCode1).'&filter=slateCoverImageFullFilter&filters_crop_x=0&filters_crop_y=0&filters_crop_w='.$width.'&filters_crop_h='.$height.'&csrfToken='.urlencode($csrft).'&returnType=json&filters_CUSTOM_MAX_HEIGHT=99999&filters_CUSTOM_MAX_WIDTH=99999&filters_rotate_t=0&persist=true';
        $pURL = 'https://www.linkedin.com/mupld/process'; $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $flds, $this->proxy); $rep = nxs_remote_post($pURL, $advSet); // prr($rep, 'IMG2');  prr($advSet, 'IMG2A'); //prr($rep);
        $ImgCode2 = json_decode($rep['body'], true); $ImgCode2 = $ImgCode2['value']; $pURL = 'https://www.linkedin.com/voyager/api/publishing/normFirstPartyArticle/'.$pID;        
        $flds = '{"patch":{"$set":{"createdAt":'.time().',"updatedAt":'.time().',"coverMedia":{"com.linkedin.voyager.publishing.CoverImage":{"croppedImage":{"com.linkedin.voyager.common.MediaProcessorImage":{"id":"'.$ImgCode2.'"}},"originalImage":{"com.linkedin.voyager.common.MediaProcessorImage":{"id":"'.$ImgCode1.'"}},"cropInfo":{"x":0,"y":0,"width":1600,"height":740},"caption":{"text":""}}},"urn":"urn:li:linkedInArticle:'.$pID.'","customPublishMessage":{"text":"X"},"authors":["urn:li:member:333698448"],"state":"PUBLISHED","title":"X","contentHtml":"X","version":0}}}'; 
        $html = str_ireplace('</p>','</p><br/>',str_ireplace('</div>','</div><br/>',$html)); $html = strip_tags($html, '<i><b><strong><br><a>');
        $ajj = json_decode($flds, true); $ajj['patch']['$set']['contentHtml'] = '<p>'.$html.'</p>'; $ajj['patch']['$set']['title'] = nsTrnc(strip_tags(nl2br($title)), 150); $ajj['patch']['$set']['customPublishMessage']['text'] = nsTrnc(strip_tags($msg), 700);        
        $flds = json_encode($ajj); //$ck = nxsMergeArraysOV($ck, $rep['cookies']);
        $hdrsArr = $this->headers('https://www.linkedin.com/post/edit/'.$pID, 'https://www.linkedin.com', 'JSON', true); $hdrsArr['Csrf-Token'] = $csrft; $hdrsArr['Accept'] = '*/*'; $hdrsArr['X-RestLi-Protocol-Version'] = '2.0.0';
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $flds, $this->proxy); $rep = nxs_remote_post($pURL, $advSet); //prr($pURL, 'pURL #2');   prr($rep); prr($advSet);
         
        $hdrsArr = $this->headers('https://www.linkedin.com/post/edit/'.$pID, 'https://www.linkedin.com', 'GET', true); $hdrsArr['Csrf-Token'] = $csrft;
        $gURL = 'https://www.linkedin.com/voyager/api/publishing/editorFirstPartyArticles/'.$pID; $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy);  $rep = nxs_remote_get($gURL, $advSet); $res = json_decode($rep['body'], true); //prr($res, 'RES');
        
        if (!empty($res) && !empty($res['firstPartyArticle']) && !empty($res['firstPartyArticle']['permalink']) ) return array('isPosted'=>'1', 'postID'=>$pID, 'postURL'=>'https://www.linkedin.com/pulse/'.$res['firstPartyArticle']['permalink'], 'pDate'=>date('Y-m-d H:i:s')); 
         else return "Post_ERROR: ".print_r($rep, true); 
    }
    function getCsrf($ck){foreach ($ck as $ci=>$cc) { if($cc->name =='JSESSIONID') return str_replace('"','',$cc->value);}}
    function adjText($txt){return str_ireplace("\r",'',str_ireplace("\n",'\n',str_replace('"','\"', nsTrnc( strip_tags($txt),700))));}
    function urlInfoBI($url) { $ck = $this->ck;  $hdrsArrA = $this->headers('https://www.linkedin.com/'); $hdrsArrA['Csrf-Token'] = $this->getCsrf($ck);  $hdrsArrA['Accept'] = 'application/json, text/javascript, */*; q=0.01';   $hdrsArrA['Accept'] = 'application/vnd.linkedin.normalized+json'; 
      $advSet = nxs_mkRemOptsArr($hdrsArrA, $ck, '', $this->proxy); $lUrl = 'https://www.linkedin.com/voyager/api/feed/urlpreview/'.urlencode($url); //prr($lUrl);
      $rep = nxs_remote_get($lUrl, $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR";  } else { $contents = json_decode($rep['body'], true); //prr($contents); // die();
      if (is_array($contents) && !empty($contents['status']) && $contents['status']=='500' ) return '500'; if (is_array($contents) && !empty($contents['included']) ) return $contents['included']; else return "Error: ".$badOut; 
      //$x = 'com.linkedin.voyager.feed.urlpreview.PreviewCreationSuccessful'; if (is_array($contents) && !empty($contents['value']) && !empty($contents['value'][$x]) ) return $contents['value'][$x]['data']; else return "Error: ".$badOut; 
      }      
    }
    function uploadImage($imgUrl, $ck){ $hdrsArr=nxs_makeHeaders($imgUrl); $advSet = nxs_mkRemOptsArr($hdrsArr, '', '', $this->proxy); $imgData = nxs_remote_get($imgUrl, $advSet); //prr($lnkArr['img']);
       if(is_nxs_error($imgData) || empty($imgData['body']) || (!empty($imgData['headers']['content-length']) && (int)$imgData['headers']['content-length']<200)) { $options['attchImg'] = 0; 
         return 'Image Error: Could not get image ('.$imgUrl.'), will post without it - Error:'.print_r($imgData, true);            
       } else $imgData = $imgData['body'];          
       $params  = "------WebKitFormFQc7dbZE\r\nContent-Disposition: form-data; name=\"file_name\"; filename=\"IMG_28898.jpg\"\r\nContent-Type: image/jpeg\r\n\r\n".$imgData."\r\n------WebKitFormFQc7dbZE--";                    
       $iurl = 'https://www.linkedin.com/mupld/slideshare/upload'; $hdrsArr = nxs_makeHeaders('http://www.linkedin.com', 'http://www.linkedin.com', 'POST', true);  unset($hdrsArr['Content-Type']);  $hdrsArr['Content-Type']='multipart/form-data; boundary=----WebKitFormFQc7dbZE'; 
       $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $params, $this->proxy); $rep = nxs_remote_post($iurl, $advSet); if (is_nxs_error($rep)) {  $badOut[] = 'Image Error: '.print_r($rep, true); } $imgID = ''; 
       if (stripos($rep['body'], '"file_key":"')===false) return 'Image Error: '.print_r($rep, true); else { $imgID = CutFromTo($rep['body'], '"file_key":"', '"'); $o = CutFromTo($rep['body'], '"original":[', ']'); $o = explode(',',$o);
         return array('i'=>$imgID, 'w'=>$o[0], 'h'=>$o[1]);
       }        
    }
    function post($msg, $lnkArr, $to){ global $nxs_plurl; $postFormType = 0; $isGrp = false; $ck = $this->ck; $to = utf8_encode($to); $parts = parse_url($to);// $this->debug = true;//$this->postToPulse(); die();           
      //## Group
      if (stripos($to, 'groups/')!==false) { $cID = preg_replace("/[^0-9]/","",$to); $to = 'https://www.linkedin.com/communities-api/v1/discussion?groupId='.$cID; $msg = str_replace("\n",'\\n', str_replace("\r",'',str_replace("\r\n","\n",$msg)));
         $hdrsArr = $this->headers('https://www.linkedin.com', 'https://www.linkedin.com', 'JSON', true); 
         foreach ($ck as $ci=>$cc) { if($cc->name =='JSESSIONID') $hdrsArr['Csrf-Token'] = str_replace('"','',$cc->value);} $hdrsArr['Accept'] = 'application/json, text/javascript, */*; q=0.01';         
         $imgCid = ''; if ($lnkArr['postType']=='I') {  $imgArr = $this->uploadImage($lnkArr['img'], $ck);  if (!is_array($imgArr)) { $badOut[] = 'Image Error: '.$imgArr; } else $imgCid = ',"contentId":"'.$imgArr['i'].'"'; }// prr($imgCid); die();
         if ($lnkArr['postType']=='A') { $UU = 'https://www.linkedin.com/communities-api/v1/url-preview/'.urlencode($lnkArr['url']); $pURL = 'https://www.linkedin.com/communities-api/v1/discussion?groupId='.$cID;
           $advSetU = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $repU = nxs_remote_get($UU, $advSetU); if (is_nxs_error($repU)) { $badOut = "ERROR (Preview) ".print_r($repU, true); return $badOut; }  $ctU = $repU['body'];
           while (stripos($ctU,'"status":"KEEP_POLLING"')!==false) { sleep(5); $repU = nxs_remote_get($UU, $advSetU); $ctU = $repU['body']; } //prr($ctU);          
           if (stripos($ctU,'"status":"FAILED"')!==false) { $flds = '{"communityId":"'.$cID.'","contentType":"TEXT","comments":[],"mentions":[],"activityType":"DISCUSSION","title":"'.str_replace('"','\"',$lnkArr['postTitle']).'","body":"'.str_replace('"','\"',strip_tags($msg)).'\\n\\n'.$lnkArr['url'].'"}'; }
           elseif (stripos($ctU,'"status":"SUCCEEDED"')!==false) { $lid = CutFromTo($ctU,'"urn:li:ingestedContent:','"');  $ctU = str_replace('\"','ZZZ!==X==!ZZZ',$ctU);  $cdesc = CutFromTo($ctU,'"description":"','"'); $cttl = CutFromTo($ctU,'"title":"','"'); 
             $flds = '{"communityId":"'.$cID.'","contentType":"LINK_SHARE","comments":[],"mentions":[],"activityType":"DISCUSSION","title":"'.str_replace('"','\"',$lnkArr['postTitle']).'","body":"'.str_replace('"','\"',strip_tags($msg)).'","contentId":"urn:li:ingestedContent:'.$lid.'","contentTitle":"'.str_replace('"','\"',$cttl).'","contentBody":"'.str_replace('"','\"',$cdesc).'"}'; 
             $flds = str_replace('ZZZ!==X==!ZZZ', '\"',$flds);
           }
         } else $flds = '{"communityId":"'.$cID.'","contentType":"'.(empty($imgCid)?'TEXT':'RICH_MEDIA_SHARE').'","comments":[],"mentions":[],"activityType":"DISCUSSION","title":"'.str_replace('"','\"',$lnkArr['postTitle']).'","body":"'.str_replace('"','\"',strip_tags($msg)).'"'.$imgCid.'}';        
      } else {
      //## Company & Profile
        if (stripos($to, 'company/')!==false) { $pType= 'c'; $to = str_replace('?','/',str_replace('&','/',$to)).'/'; $ccid = CutFromTo($to,'company/','/'); 
          $act = '{"com.linkedin.voyager.feed.CompanyActor":{"id":"'.$ccid.'","showFollowAction":false,"miniCompany":{"id":"'.$ccid.'","trackingId":"sw9WkK51SLulTK7EAe8yQg==","objectUrn":"urn:li:company:'.$ccid.'","entityUrn":"urn:li:fs_miniCompany:'.$ccid.'","name":"CouponsWP","showcase":false,"active":true,"type":"entities/shared/mini-company"},"followingInfo":{"id":"urn:li:company:'.$ccid.'","entityUrn":"urn:li:fs_followingInfo:urn:li:company:'.$ccid.'","following":true,"followerCount":1},"flavors":[]}}';
        }
        if (stripos($to, 'linkedin.com/home')!==false) { $pType= 'p'; 
          $hdrsArr=nxs_makeHeaders($to); $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get('https://www.linkedin.com/feed/', $advSet);  if (is_nxs_error($rep)) {  $badOut[] = 'User Page Error: '.print_r($rep, true); }
          $mid = CutFromTo($rep['body'],'urn:li:fs_miniProfile:','&quot'); $aid = CutFromTo($rep['body'],';,&quot;plainId&quot;:',',&quot'); $pid = CutFromTo($rep['body'],'publicIdentifier&quot;:&quot;','&quot');  
          $act = '{"com.linkedin.voyager.feed.MemberActor":{"id":"'.$mid.'","showFollowAction":false,"miniProfile":{"id":"'.$mid.'","trackingId":"dYt2bMlRQiafmL2fYKcz7w==","objectUrn":"urn:li:member:'.$aid.'","entityUrn":"urn:li:fs_miniProfile:'.$mid.'","firstName":"V","lastName":"H","occupation":"Freelancer","publicIdentifier":"'.$pid.'"},"skillNames":[]}}'; //prr($act);
        }
        $to = 'https://www.linkedin.com/voyager/api/feed/shares?action=create'; $txt = json_encode(array('value'=>$msg)); $cnt = '{"com.linkedin.voyager.feed.ShareText":{"text":{"values":['.$txt.']}}}';
        if (!empty($lnkArr['postType']) && $lnkArr['postType']=='I' && !empty($lnkArr['img'])) {  $txt = array('values'=>array()); $txt['values'][] = array('value'=>$msg); $imgArr = $this->uploadImage($lnkArr['img'], $ck);          
          if (!is_array($imgArr)) { $badOut[] = 'Image Error: '.$imgArr; } else { $imgID = $imgArr['i'];
            $img = array('com.linkedin.voyager.common.MediaProxyImage'=>array('url'=>'http://image-store.slidesharecdn.com/'.str_ireplace('.','-original.',$imgID),'originalWidth'=>(int)($imgArr['w']),'originalHeight'=>(int)($imgArr['h'])));       
            $cntArr = array('com.linkedin.voyager.feed.ShareImage'=>array('contentType'=>'image/jpeg', 'fileId'=>$imgID,'text'=>$txt,'image'=>$img)); $cnt = json_encode($cntArr, JSON_UNESCAPED_SLASHES); 
          }
        } elseif (!empty($lnkArr['postType']) && $lnkArr['postType']=='A' && !empty($lnkArr['url'])) { $cx = $this->urlInfoBI($lnkArr['url']); if (!empty($cx) && is_array($cx)) { foreach ($cx as $cc) if (!empty($cc) && !empty($cc['id'])) { $lcnt = $cc; break; } 
          if (is_array($lcnt)) { if (empty($lcnt['description'])) $lcnt['description'] = $lcnt['title'];  $txt = array('values'=>array()); $txt['values'][] = array('value'=>$msg); 
            if (!empty($cx[0])&&!empty($cx[0]['url']))  $img = array('com.linkedin.voyager.common.MediaProxyImage'=>array('id'=>$lcnt['previewImages'][0],'url'=>$cx[0]['url'],'originalWidth'=>$cx[0]['originalWidth'],'originalHeight'=>$cx[0]['originalHeight'])); else $img = array();
            $cntArr = array('com.linkedin.voyager.feed.ShareArticle'=>array('urn'=>$lcnt['urn'],'title'=>$this->adjText($lcnt['title']),'description'=>$this->adjText($lcnt['description']),'url'=>$lcnt['url'],'text'=>$txt,'image'=>$img,'articleType'=>'REGULAR','id'=>$lcnt['urn'])); 
            $cnt = json_encode($cntArr); 
          } 
        } elseif($cx=='500') {  $txt = json_encode(array('value'=>$msg."\r\n".$lnkArr['url'])); $cnt = '{"com.linkedin.voyager.feed.ShareText":{"text":{"values":['.$txt.']}}}'; } }
        $flds = '{"update":{"isHidden":false,"updatePosition":1,"isSponsored":false,"premium":false,"socialDetail":{"liked":false,"commentingDisabled":false,"showShareButton":false,"quickComments":[]},"value":{"com.linkedin.voyager.feed.ShareUpdate":{"shareAudience":"PUBLIC","edited":false,"targetingOutOfNetwork":false,"content":'.$cnt.',"actions":[],"actor":'.$act.'}},"highlightedLikes":[],"highlightedComments":[]}}';  // $j = json_decode($flds, true); prr($j); //die();
      }
      $hdrsArr = $this->headers('https://www.linkedin.com', 'https://www.linkedin.com', 'JSON', true); $hdrsArr['Content-Type'] = 'application/json; charset=UTF-8'; $hdrsArr['X-RestLi-Protocol-Version'] = '2.0.0';
      $hdrsArr['X-LI-Track'] = '{"clientVersion":"1.1.*","osName":"web","timezoneOffset":-4,"deviceFormFactor":"DESKTOP","mpName":"voyager-web"}'; $hdrsArr['X-LI-Lang'] = 'en_US';
      $hdrsArr['X-li-page-instance'] = 'urn:li:page:d_flagship3_company_admin;2/EuL/3ATQCpL19RzVjQUA=='; $hdrsArr['Accept'] = '*/*';    
      foreach ($ck as $ci=>$cc) { if($cc->name =='JSESSIONID') $hdrsArr['Csrf-Token'] = str_replace('"','',$cc->value);} 
      $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $flds, $this->proxy); $rep = nxs_remote_post($to, $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR"; return $badOut; } // prr($to); prr($advSet);  prr($rep, 'POST RESULT');
      if ($rep['response']['code']=='200' && stripos($rep['body'], '"responseStatus":"CREATED"')!==false ) { $ct = $rep['body']; $pid = CutFromTo($ct, 'activityId":"', '"'); $purl = 'https://www.linkedin.com/groups/'.$cID.'/'.$pid; 
        return array('isPosted'=>'1', 'postID'=>$pid, 'postURL'=>$purl, 'pDate'=>date('Y-m-d H:i:s'), 'ck'=>$ck); //## Ok Group   
      } elseif ($rep['response']['code']=='201' && stripos($rep['body'], '"permalink":"')!==false ) { $ct = $rep['body']; $pid = CutFromTo($ct, '"id":"activity:', '"'); $purl = CutFromTo($ct, '"permalink":"', '"'); 
        return array('isPosted'=>'1', 'postID'=>$pid, 'postURL'=>$purl, 'pDate'=>date('Y-m-d H:i:s'), 'ck'=>$ck); //## Ok Profile and Page
      } else { return "[Company Page] Post Problem: ".print_r($rep, true); }   
    }
}}
//================================Flipboard=====================================
if (!class_exists('nxsAPI_FP')){class nxsAPI_FP{ var $ck = array(); var $tk=''; var $u=''; var $debug = false; var $proxy = array(); var $sid = ''; var $cuid = ''; 
    function headers($ref, $org='', $post=false, $aj=false){ $hdrsArr = array(); 
      $hdrsArr['Cache-Control']='max-age=0'; $hdrsArr['Connection']='keep-alive'; $hdrsArr['Referer']=$ref;
      $hdrsArr['User-Agent']='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36'; 
      if($post==='j') $hdrsArr['Content-Type']='application/json;charset=UTF-8'; elseif($post===true) $hdrsArr['Content-Type']='application/x-www-form-urlencoded';
      if($aj===true) $hdrsArr['X-Requested-With']='XMLHttpRequest';  if ($org!='') $hdrsArr['Origin']=$org; 
      $hdrsArr['Accept']='text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';// $hdrsArr['DNT']='1';
      if (function_exists('gzdeflate')) $hdrsArr['Accept-Encoding']='gzip,deflate,sdch'; 
      $hdrsArr['Accept-Language']='en-US,en;q=0.8'; return $hdrsArr; 
    }
    function setSession(){
      if (!empty($this->sid)) { if (empty($this->ck)) $this->ck = array(); if ($this->debug) echo "[FP] Setting Session...<br/>\r\n"; 
          foreach ($this->ck as $ci=>$cc) { if ( $this->ck[$ci]->name=='access_token') unset($this->ck[$ci]); if ( $this->ck[$ci]->name=='userid') unset($this->ck[$ci]); }
          $c = new NXS_Http_Cookie( array('name' => 'access_token', 'value' => $this->sid) ); $this->ck[] = $c; $c = new NXS_Http_Cookie( array('name' => 'userid', 'value' => $this->cuid) ); $this->ck[] = $c; 
      } 
    }
    function check($u=''){ $this->setSession(); $ck = $this->ck; if (!empty($ck) && is_array($ck)) { $usr = 'hre'; if ($this->debug) echo "[FP] Checking user ".$u."...<br/>\r\n"; 
      $hdrsArr = $this->headers('https://flipboard.com/');  $advSet = nxs_mkRemOptsArr($hdrsArr); $rep = nxs_remote_get( 'https://flipboard.com/', $advSet); $ck = nxs_MergeCookieArr($rep['cookies'], $ck);
      $hdrsArr = $this->headers('https://flipboard.com/profile');  $advSet = nxs_mkRemOptsArr($hdrsArr,$ck); $rep = nxs_remote_get( 'https://flipboard.com/profile', $advSet);// prr($advSet); prr($rep);
      if (is_nxs_error($rep)) return false; if (stripos($rep['body'],'"authorUsername":"')!==false) { $usr = trim(strip_tags(CutFromTo($rep['body'], '"authorUsername":"', '"'))); } else return false;
        if (stripos($rep['body'],'"email":"')!==false) { $usrE = trim(strip_tags(CutFromTo($rep['body'], '"email":"', '"'))); }
        if (empty($u) || $u==$usr || $u==$usrE) { $this->ck = $ck; return true; } else return false;
      } else return false;
    }
    function connect($u,$p){ $badOut = 'Error: '; // $this->debug = true;
      //## Check if alrady IN
      if (!$this->check($u)){ if ($this->debug) echo "[FP] NO Saved Data; Logging in...<br/>\r\n";  $url = "";  $hdrsArr = $this->headers('');
        $advSet = nxs_mkRemOptsArr($hdrsArr); $rep = nxs_remote_get('https://flipboard.com/signin', $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - =1= ERROR"; return $badOut; } 
        $ck = $rep['cookies']; $rTok = CutFromTo($rep['body'], 'id="_csrf" type="hidden" value="', '"');// $rTok = str_replace('&#x2f;','/',$rTok);
        $hdrsArr = $this->headers('https://flipboard.com/', 'https://flipboard.com', true,true);  $flds = array('username' => $u, 'password' => $p, '_csrf' => $rTok); $flds = http_build_query($flds);
        $advSet = nxs_mkRemOptsArr($hdrsArr,$ck,$flds); $response = nxs_remote_post('https://flipboard.com/api/flipboard/login', $advSet); //prr($advSet);  prr($response);// die();
        if (is_nxs_error($response)) {  $badOut = print_r($response, true)." - ERROR"; return $badOut; } $ck =  $response['cookies'];       
        if (!empty($response['body']) && stripos($response['body'], 'id="errormessage"')!==false) { $errMsg = CutFromTo($response['body'],'id="errormessage"','/p>'); $errMsg = CutFromTo($errMsg,'>','<'); return $errMsg; }  
        if (stripos($response['body'], '"success":true')!==false) { $this->ck = $ck; return false; }  
        if (stripos($response['body'], 'success":false,"code":401')!==false) { return 'Flipboard Login Error: 401 - No Access (RC-NR), please try <a href="http://nxs.fyi/fpac" target="_blank">Alternative Flipboard Configuration - http://nxs.fyi/fpac</a>'; }  
        if (isset($response['headers']['location']) && ( $response['headers']['location']=='https://editor.flipboard.com/' || $response['headers']['location']=='/')) { 
        $hdrsArr = $this->headers('https://editor.flipboard.com/'); $advSet = nxs_mkRemOptsArr($hdrsArr,$ck); $rep = nxs_remote_get( 'https://flipboard.com/profile/', $advSet); 
        if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR"; return $badOut; } $mh = trim(strip_tags(CutFromTo($rep['body'], '<a href="/account">', '</a>'))); $this->ck = $ck; return false;    
      } else  $badOut = print_r($response, true)." - ERROR"; return $badOut; 
    }}

    function post($post){ $ck = $this->ck; $hdrsArr = $this->headers('https://editor.flipboard.com/'); $badOut = array();  if ($this->debug) echo "[FP] Posting to user ".$post['mgzURL']."...<br/>\r\n"; 
      $advSet = nxs_mkRemOptsArr($hdrsArr,$ck); $rep = nxs_remote_get('https://share.flipboard.com/bookmarklet/popout?v=2&title=&url='.urlencode($post['url']).'&t=', $advSet);  //prr($rep);
      if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR 1"; return $badOut; } $rTok = CutFromTo($rep['body'], 'id="fl-csrf">&quot;', '&quot;'); 
      $rTok = str_replace('&#x2f;','/',$rTok); if (empty($rTok)) return "Error: ".strip_tags($rep['body']);// $ck =   $rep['cookies']; 
      if (!empty($rep['cookies'])) foreach ($rep['cookies'] as $ccN) { $fdn = false; foreach ($ck as $ci=>$cc) if ($ccN->name == $cc->name) { $fdn = true; $ck[$ci] = $ccN;  } if (!$fdn) $ck[] = $ccN; }  
      $flds = array("url"=>$post['url'], "_csrf"=>$rTok); if (version_compare(phpversion(), '5.4.0', '<')) $flds = str_replace('\\/', '/', json_encode($flds)); else $flds = json_encode($flds, JSON_UNESCAPED_SLASHES); 
      $hdrsArr = $this->headers('https://share.flipboard.com/bookmarklet/popout', 'https://share.flipboard.com', 'j'); 
      $hdrsArr['Accept'] = 'application/json, text/plain, */*'; $advSet = nxs_mkRemOptsArr($hdrsArr,$ck,$flds); $response = nxs_remote_post( 'https://share.flipboard.com/bookmarklet/flip', $advSet);  //prr($advSet); prr($response);
      if (is_nxs_error($rep)) {  $badOut = print_r($response, true)." - ERROR 2"; return $badOut; } 
      if (stripos($response['body'], '"success":true')!==false) { $txtArr = json_decode($response['body'], true); 
        if (stripos($post['mgzURL'],'@')!==false){ $advSet = nxs_mkRemOptsArr($hdrsArr,$ck,'','',1); $rep = nxs_remote_get($post['mgzURL'],$advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR 01"; return $badOut; }  
          $mgzURL = CutFromTo($rep['body'], '"magazineTarget":"', '"');  $sccID = 'auth/'.CutFromTo($rep['body'], '"remoteidToShare":"', '"');    
        }elseif (stripos($post['mgzURL'],'auth/flipboard/curator')!==false) { 
          $mgzURL = $post['mgzURL']; $mgzURL = 'flipboard/mag-'.str_replace('-','%252D', urldecode(CutFromTo($mgzURL."|||", 'magazine%252F', '|||'))); 
          $sccID = $post['mgzURL']; $sccID = urldecode(CutFromTo($sccID."|||", 'section?sections=', '|||'));
        } else return "Incorrect Flipboard URL";    
        $flds = array("url"=>$post['url'],"sig"=>$txtArr['sig'],"image"=>$post['imgURL'],"price"=>null,"currency"=>'$',"title"=>'',"text"=>$post['text'],"target"=>$mgzURL,"services"=>"","_csrf"=>$rTok); // prr($flds);
        $flds = json_encode($flds); $flds = str_replace('\/','/',$flds); $hdrsArr = $this->headers('https://share.flipboard.com/bookmarklet/popout', 'https://share.flipboard.com', 'j');      
        $advSet = nxs_mkRemOptsArr($hdrsArr,$ck,$flds); $response = nxs_remote_post('https://share.flipboard.com/bookmarklet/save', $advSet); 
        if (stripos($response['body'], '"success":true')!==false) { sleep(2); 
          $flds = array("sectionid"=>$sccID, "title"=>'', "imageURL"=>$post['imgURL'], "_csrf"=>$rTok); $flds = json_encode($flds); $flds = str_replace('\/','/',$flds); //prr($flds);
          $advSet = nxs_mkRemOptsArr($hdrsArr,$ck,$flds); $resp2 = nxs_remote_post( 'https://share.flipboard.com/v1/social/shortenSection', $advSet);// prr($resp2);
          $respLink  = json_decode($resp2['body'], true); $respLink = $respLink['result']; $respID = str_replace('http://flip.it/', '', $respLink);
          return array('postID'=>$respID, 'isPosted'=>1, 'postURL'=> $respLink, 'pDate'=>date('Y-m-d H:i:s'), 'ck'=>$ck);          
        } else { $badOut['Error'] .= print_r($response, true); return $badOut; }          
      } else return "Error: ".strip_tags($response['body']);   
    } 
}} 
//================================Reddit========================================
if (!class_exists('nxsAPI_RD')){class nxsAPI_RD{ var $ck = array(); var $mh=''; var $srList=''; var $u=''; var $debug = false; var $proxy = array();    
    function check($u=''){ $ck = $this->ck; if (!empty($ck) && is_array($ck)) { if ($this->debug) echo "[RD] Checking user ".$u."...<br/>\r\n"; 
      $hdrsArr = nxs_getNXSHeaders('https://www.reddit.com/prefs/update/'); $advSet = nxs_mkRemOptsArr($hdrsArr,$ck, '', $this->proxy); $rep = nxs_remote_get( 'https://www.reddit.com/prefs/update/', $advSet);
      if (is_nxs_error($rep)) return false; if (stripos($rep['body'],'"logged": "')!==false) { $usr = trim(strip_tags(CutFromTo($rep['body'], '"logged": "', '"'))); $this->mh = trim(strip_tags(CutFromTo($rep['body'], '"modhash": "', '"'))); } else return false;
        if (empty($u) || $u==$usr) return true; else return false;
      } else return false;
    }
    function connect($u,$p){ $badOut = 'Error: '; // $this->debug = true;
      //## Check if alrady IN
      if (!$this->check($u)){ if ($this->debug) echo "[RD] NO Saved Data; Logging in...<br/>\r\n"; $url = "https://www.reddit.com/api/login/".$u;  $hdrsArr = nxs_getNXSHeaders('https://www.reddit.com'); 
        $flds = array('api_type' => 'json', 'user' => $u, 'passwd' => $p, 'op'=>'login-main', 'rem'=>'on'); $advSet = nxs_mkRemOptsArr($hdrsArr,'',$flds, $this->proxy); $response = nxs_remote_post( $url, $advSet); //prr($response);
        if (is_nxs_error($response)) { "|ERROR [LOGIN 01]:".$badOut = print_r($response, true); return $badOut; } $this->ck =  $response['cookies']; $respb = json_decode($response['body'], true);  
        if (!is_array($respb) && stripos($response['body'],'</style>')!==false) return strip_tags(CutFromTo($response['body'].'==-|-==', '</style>', '==-|-==')); 
        if (is_array($respb['json']['errors']) && count($respb['json']['errors'])>0 ) {  $badOut = "|ERROR [LOGIN 02]:".print_r($respb, true); return $badOut; } 
        $data = $respb['json']['data']; $this->mh = $data['modhash']; return false;
      } else { if ($this->debug) echo "[RD] Saved Data is OK;<br/>\r\n"; return false; }
    }
    function getSubReddits($curr=''){ $hdrsArr = nxs_getNXSHeaders('https://www.reddit.com'); $advSet = nxs_mkRemOptsArr($hdrsArr,$this->ck, '', $this->proxy); $response = nxs_remote_get( 'https://www.reddit.com/subreddits/mine/moderator/', $advSet); 
        $cnt = $response['body']; $cnt = CutFromTo($cnt, '<div id="siteTable"', '<div class="footer-parent">'); $srds = '';
        $cntArr = explode('<p class="titlerow">',$cnt); foreach ($cntArr as $txt) if (stripos($txt, 'class="title"')!==false) { $bid = CutFromTo($txt, '://www.reddit.com/r/', '/"'); $bname = trim(CutFromTo($txt, 'class="title" >', '</a>'));
          if (isset($bid)) $srds .= '<option '.($curr==$bid?'selected="selected"':'').' value="'.$bid.'">'.trim($bname).'</option>';
        } $this->srList = $srds;   
    }

    function post($msg, $title, $sr, $url){ if ($this->debug) echo "[RD] Posting...<br/>\r\n"; $hdrsArr = nxs_getNXSHeaders('https://www.reddit.com'); 
      if (stripos($sr,'/user/')!==false) $post = array('uh'=>$this->mh, 'title'=>$title, 'submit_type'=>'profile', 'selected_sr_names'=>'', 'renderstyle'=>'html', 'id'=>'23newlink', 'r'=>'u_'.CutFromTo($sr.'/','/user/','/'));
        else $post = array('uh'=>$this->mh, 'sr'=>$sr, 'title'=>$title, 'save'=>true);
      if (!empty($url)) { $post['url'] = $url; $post['kind']='link'; $retNum = 16; } else { $post['text'] = $msg; $post['kind']='self'; $retNum = 10; }         
      $url = "https://www.reddit.com/api/submit"; $advSet = nxs_mkRemOptsArr($hdrsArr,$this->ck, $post, $this->proxy); $advSet['extension']='json'; $response = nxs_remote_post($url, $advSet); // prr($advSet); //prr($response);
      if (is_nxs_error($response)) {  $badOut['Error'] = "|ERROR [POST 01]:".print_r($response, true); return $badOut; } $rJSN = json_decode($response['body'], true); $rdNewPostID = 'https://www.reddit.com';       
      if (!isset($rJSN['jquery']) || !is_array($rJSN['jquery'])) { $badOut['Error'] = "|ERROR [POST 02]:".print_r($response, true); return $badOut; } 
      $r = $rJSN['jquery']; $chK = isset($r[$retNum]) && is_array($r[$retNum][3]) && count($r[$retNum][3])>0;  if ($chK && stripos($r[$retNum][3][0], 'https://')!==false) $rdNewPostID = $r[$retNum][3][0];             
      if ($chK && stripos($r[$retNum][3][0], 'already_submitted')!==false ) $rdNewPostID .= str_ireplace('?already_submitted=true', '', $r[$retNum][3][0]); 
      elseif ($chK && stripos($r[$retNum][3][0], 'error.BAD_CAPTCHA')!==false ) { $badOut['Error'] = 'ERROR: Post Rejected. CAPTCHA Required. Reddit thinks that you don\'t have rights to post here without CAPTCHA.<br/><a href="http://nxs.fyi/faq72" target="_blank">Please see FAQ #7.2</a>'; return $badOut; } 
      elseif ($chK && stripos($r[$retNum][3][0], 'error')!==false ) { $badOut['Error'] = "|ERROR [POST 03]: ".$r[$retNum][3][0]; return $badOut; } 
      elseif ($chK && stripos($r[$retNum][3][0], 'https://')===false) { $badOut['Error'] = "|ERROR [POST 04]:".print_r($r[$retNum][3][0], true); return $badOut; }       
      if ($rdNewPostID!='https://www.reddit.com') { $this->ck = nxs_MergeCookieArr($this->ck, $response['cookies']); return array('postID'=>$rdNewPostID, 'isPosted'=>1, 'postURL'=>$rdNewPostID, 'pDate'=>date('Y-m-d H:i:s')); } 
        else { $badOut['Error'] = '|ERROR [POST 05]: '.print_r($response, true); return $badOut; }
      return $badOut;   
    } 
}} 
//================================Instagram=====================================
if (!class_exists('nxsAPI_IG')){class nxsAPI_IG{ var $ck = array(); var $agent=''; var $guid=''; var $phid='';  var $dId=''; var $tkn = ''; var $opNm = ''; var $debug = false; var $loc = ''; var $proxy = array(); var $u=''; var $p=''; var $sid=''; 
    function __construct() { $this->agent = 'Instagram 12.0.0.7.91 Android (21/5.0; 480dpi; 1080x1920; samsung; SM-G900P; kltespr; qcom; en_US)';
      $this->guid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',  mt_rand(0, 65535),  mt_rand(0, 65535),  mt_rand(0, 65535),  mt_rand(16384, 20479),  mt_rand(32768, 49151),  mt_rand(0, 65535),  mt_rand(0, 65535),  mt_rand(0, 65535));      
      $this->dId = "android-".$this->guid;
    }
    function headers($ref, $org='', $type='GET', $aj=false){$hdrsArr = array(); $hdrsArr['User-Agent']='Mozilla/5.0 (Linux; U; Android 4.3; en-us; SM-N900T Build/JSS15J) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30';
      $hdrsArr['connection']='keep-alive'; $hdrsArr['Accept-Language']='en-US'; $hdrsArr['Accept-Encoding']='gzip, deflate'; if (!empty($ref)) $hdrsArr['Referer']=$ref;
      if($aj===true) $hdrsArr['X-Requested-With']='XMLHttpRequest'; if($type=='POST') $hdrsArr['Content-Type']='application/x-www-form-urlencoded';
      //$hdrsArr['X-IG-Capabilities']='3QI='; $hdrsArr['X-IG-Connection-Type']='WIFI';
    return $hdrsArr;}    
    function doSig($data) { return hash_hmac('sha256', $data, 'b4946d296abf005163e72346a6d33dd083cadde638e6ad9c5eb92e381b35784a'); }
    function makeSQExtend( $imgSrc, $thumbFile, $thumbSize=1000 ){ $type = substr( $imgSrc , strrpos( $imgSrc , '.' )+1 ); 
      switch( $type ){ case 'jpg' : case 'jpeg' : $src = imagecreatefromjpeg( $imgSrc ); break; case 'png' : $src = imagecreatefrompng( $imgSrc ); break; case 'gif' : $src = imagecreatefromgif( $imgSrc ); break; }       
      list($w, $h) = getimagesize($imgSrc); if ($w > $h)  $bgSide = $w; else { $bgSide = $h; } if ($thumbSize<$bgSide) $sqSize = $thumbSize; else $sqSize = $bgSide; //$width = imagesx( $src ); $height = imagesy( $src );
      if($w> $h) { $width_t=$sqSize; $height_t=round($h/$w*$sqSize); $off_y=ceil(($width_t-$height_t)/2); $off_x=0; } 
        elseif($h> $w) { $height_t=$sqSize; $width_t=round($w/$h*$sqSize); $off_x=ceil(($height_t-$width_t)/2); $off_y=0; } else { $width_t=$height_t=$sqSize; $off_x=$off_y=0; }
      $new = imagecreatetruecolor( $sqSize , $sqSize ); $bg = imagecolorallocate ( $new, 255, 255, 255 ); imagefill ( $new, 0, 0, $bg ); imagecopyresampled( $new , $src , $off_x, $off_y, 0, 0, $width_t, $height_t, $w, $h ); 
      $res = imagejpeg( $new , $thumbFile, 100); @imagedestroy( $new ); @imagedestroy( $src );
    }
    function makeSQCrop( $imgSrc, $thumbFile, $thumbSize=1000 ){ list($width, $height) = getimagesize($imgSrc); $type = substr( $imgSrc , strrpos( $imgSrc , '.' )+1 ); 
      switch( $type ){ case 'jpg' : case 'jpeg' : $src = imagecreatefromjpeg( $imgSrc ); break; case 'png' : $src = imagecreatefrompng( $imgSrc ); break; case 'gif' : $src = imagecreatefromgif( $imgSrc ); break; }   
      if ($width > $height) { $y = 0; $x = ($width - $height) / 2;} else { $x = 0; $y = ($height - $width) / 2;} $minSide = min($width,$height);
      $thumb = imagecreatetruecolor($minSide, $minSide); imagecopyresampled($thumb, $src, 0, 0, $x, $y, $minSide, $minSide, $minSide, $minSide);
      unlink($imgSrc); imagejpeg($thumb,$thumbFile); @imagedestroy($src); @imagedestroy($thumb);
    }
    function makeJpg( $imgSrc, $thumbFile, $thumbSize=1000 ){ list($width, $height) = getimagesize($imgSrc); $type = substr( $imgSrc , strrpos( $imgSrc , '.' )+1 );  
      switch( $type ){ case 'jpg' : case 'jpeg' : $src = imagecreatefromjpeg( $imgSrc ); break; case 'png' : $src = imagecreatefrompng( $imgSrc ); break; case 'gif' : $src = imagecreatefromgif( $imgSrc ); break; }         
      unlink($imgSrc); imagejpeg($src,$thumbFile); @imagedestroy($src); @imagedestroy($thumb);
    }
    function setSession(){
      if (!empty($this->sid)) { if (empty($this->ck)) $this->ck = array(); if ($this->debug) echo "[FP] Setting Session...<br/>\r\n"; 
          foreach ($this->ck as $ci=>$cc) if ( $this->ck[$ci]->name=='sessionid') unset($this->ck[$ci]); $c = new NXS_Http_Cookie( array('name' => 'sessionid', 'value' => $this->sid) ); $this->ck[] = $c; 
      } 
    }
    function check($u=''){ $this->setSession(); $ck = $this->ck; if (!empty($ck) && is_array($ck)) { $hdrsArr = $this->headers('https://www.instagram.com/accounts/edit/','','GET',true); if ($this->debug) echo "[IG] Checking....<br/>\r\n";
        foreach ($ck as $ci=>$cc) { if ( $ck[$ci]->name=='sessionid' && empty($ck[$ci]->value)) unset($ck[$ci]); } 
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get('https://www.instagram.com/accounts/edit/?__a=1', $advSet); // if ($this->debug) prr($advSet);
        if (is_nxs_error($rep)) return false;  $contents = $rep['body']; // if ($this->debug) prr($rep); //die();
        if ($rep['response']['code']=='302' && $rep['headers']['location']=='https://www.instagram.com/challenge/?__a=1') return 'c'; if ($rep['response']['code']=='302')  return false;        
        $jc = json_decode($contents, true); if (!empty($jc) && is_array($jc) && !empty($jc['form_data']) && is_array($jc['form_data']) && (!empty($jc['form_data']['username']) || !empty($jc['form_data']['email']))) {
           $this->prcCK($rep['cookies']); $usr = $jc['form_data']['username']; $usrEml = $jc['form_data']['email'];  if ($this->debug) echo "[IG] Logged as: ".$usr." (".$usrEml.")<br/>\r\n"; return true; 
        } else  return false;       
      } else return false;
    }
    function prcCK($ck){ if (empty($this->ck)) $this->ck = array(); foreach ($ck as $ci=>$cc) { if ( $ck[$ci]->name=='sessionid' && empty($ck[$ci]->value)) unset($ck[$ci]); } 
      foreach ($this->ck as $ci=>$cc) { if ( $this->ck[$ci]->name=='sessionid' && empty($this->ck[$ci]->value)) unset($this->ck[$ci]); }  
      foreach ($ck as $ci=>$cc) { if ( $ck[$ci]->name=='csrftoken') $this->tkn = $ck[$ci]->value; 
      //if ( $ck[$ci]->name=='sessionid') $ck[$ci]->value = urlencode($cc->value); 
      if ( $ck[$ci]->name=='checkpoint_step') unset($ck[$ci]); } $this->ck = nxs_MergeCookieArr($this->ck, $ck);
    }   
    function bldBody($arr){ $body = "";
      foreach($arr as $b){ $body .= "--".str_replace('-','',$this->guid)."\r\n"; $body .= "Content-Disposition: ".$b["type"]."; name=\"".$b["name"]."\"";
        if(isset($b["filename"])) { $ext = pathinfo($b["filename"], PATHINFO_EXTENSION); $body .= "; filename=\"".substr(bin2hex($b["filename"]),0,18).".".$ext."\""; }
        if(isset($b["headers"]) && is_array($b["headers"])) foreach($b["headers"] as $header)$body.= "\r\n".$header; $body.= "\r\n\r\n".$b["data"]."\r\n";
      } $body .= "--".str_replace('-','',$this->guid)."--"; return $body;
    }        
    function connect($u='',$p=''){ $badOut = 'Error: '; if (!empty($u)) $this->u = $u; if (!empty($p)) $this->p = $p; $u = $this->u; $p = $this->p; $chk = $this->check($u); 
      if ($chk==='c') return "You've got a login checkpoint! Please login to Instagram from your phone and confirm the login or action before trying to post again.";
      //## Check if alrady IN
      if ($chk===true) { if ($this->debug) echo "[IG] Saved Data is OK;<br/>\r\n"; return false; } else  return $this->connectW();      
    }
    function connectW($u='',$p=''){ $badOut = 'Error: '; if (!empty($u)) $this->u = $u; if (!empty($p)) $this->p = $p; $u = $this->u; $p = $this->p;
      if ($this->debug) echo "[IG] NO Saved Data; Logging in...<br/>\r\n";  $this->ck = array();      
        $advSet = nxs_mkRemOptsArr( $this->headers('https://www.instagram.com/',''), '', '', $this->proxy); $rep = nxs_remote_get('https://www.instagram.com/', $advSet); $this->prcCK($rep['cookies']);  $flds = array('username'=>$u, '&password'=>$p);
        //## ACTUAL LOGIN 
        $ck = $this->ck;  $hdrsArr =  $this->headers('https://www.instagram.com/', '', 'POST', true);  $hdrsArr['X-Instagram-AJAX'] = '1'; $hdrsArr['X-CSRFToken'] = $this->tkn; $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $flds, $this->proxy);
        unset($advSet['user-agent']);
        $rep = nxs_remote_post('https://www.instagram.com/accounts/login/ajax/', $advSet); $this->prcCK($rep['cookies']); $ck = $this->ck; //prr($advSet);
        if (is_nxs_error($rep)) {  $badOut = "|ERROR -02-".print_r($rep, true); return $badOut; } if (empty($rep['body'])) {  $badOut = "|ERROR -03-".print_r($rep, true); return $badOut; }  $obj = @json_decode($rep['body'], true); //prr($rep);
        if (empty($obj) || !is_array($obj) || empty($obj['status'])) {  $badOut = "|ERROR -04- ".print_r($rep, true); return $badOut; }        
        if ($obj['status']!='ok' && !empty($obj['message']) && $obj['message']=='checkpoint_required' && !empty($obj['checkpoint_url'])) { $this->prcCK($rep['cookies']); $ck = $this->ck;
        if (stripos($obj['checkpoint_url'], 'integrity')!==false) {
            $hdrsArr =  $this->headers('https://www.instagram.com/','https://www.instagram.com/', '', true);  $hdrsArr['X-Instagram-AJAX'] = '1'; $hdrsArr['X-CSRFToken'] = $this->tkn; $cpURL = $obj['checkpoint_url'];
            $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $flds, $this->proxy); $rep = nxs_remote_get($cpURL, $advSet); // prr($advSet); prr($rep);             
          } else {
            $flds = 'choice=1'; $hdrsArr =  $this->headers('https://www.instagram.com/','https://www.instagram.com/', $flds, true);  $hdrsArr['X-Instagram-AJAX'] = '1'; $hdrsArr['X-CSRFToken'] = $this->tkn; $cpURL = $obj['checkpoint_url'];
            $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $flds, $this->proxy); $rep = nxs_remote_post('https://www.instagram.com'.$cpURL, $advSet); //prr($advSet); prr($rep);             
            $obj = @json_decode($rep['body'], true);  if ($obj['status']=='ok') { $this->prcCK($rep['cookies']); $ck = $this->ck;
              if (function_exists('nxs_getOption')) { $opVal = array(); $opNm = $this->opNm; $opVal = nxs_getOption($opNm); $opVal['ck'] = $ck;  $opVal['url'] = $cpURL; nxs_saveOption($opNm, $opVal); }  
            } return 'cpt';
          }
        } if ($obj['status']!='ok' && !empty($obj['message']))  return "|ERROR -LOGIN- ".print_r($obj, true);          
        if ( empty($obj['authenticated']) && ( empty($obj['logged_in_user']) || empty($obj['logged_in_user']['username']))) { $repM = $this->connectM(); if ($repM==false) return false; else return "|ERROR -05.1- ".print_r($repM, true).' | '.print_r($rep, true); }
        if ($obj['status']=='ok') { $this->prcCK($rep['cookies']); return false; } else return "|ERROR -LOGIN 2- ".print_r($obj, true);  
    }
    function connectM($u='',$p=''){ if (!empty($u)) $this->u = $u; if (!empty($p)) $this->p = $p; $u = $this->u; $p = $this->p; $badOut = 'Error: '; // $this->debug = true;      
        $flds = '{"device_id":"'.$this->dId.'","guid":"'.$this->guid.'","username":"'.$u.'","password":"'.$p.'","Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"}'; $flds = 'signed_body='.$this->doSig($flds).'.'.urlencode($flds).'&ig_sig_key_version=4';
        //## ACTUAL LOGIN 
        $hdrsArr = $this->headers('', '', 'POST'); $hdrsArr['User-Agent']=$this->agent;  $advSet = nxs_mkRemOptsArr($hdrsArr, '', $flds, $this->proxy); $rep = nxs_remote_post('https://i.instagram.com/api/v1/accounts/login/', $advSet); //prr($advSet); prr($rep);        
        $obj = @json_decode($rep['body'], true); $this->prcCK($rep['cookies']); $ck = $this->ck; if ($obj['status']=='ok') return false;
        if ($obj['status']!='ok' && !empty($obj['message'])) return 'IG M Error (Message):'.print_r($obj['message'], true)." | ".(!empty($obj['error_type'])?print_r($obj['error_type'], true):''); 
        if ($obj['status']!='ok' && !empty($obj['message']) && $obj['message']=='checkpoint_required' && !empty($obj['checkpoint_url'])) { $hdrsArr = $this->headers('https://www.instagram.com'); 
           $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $lrep = nxs_remote_get("https://i.instagram.com/integrity/checkpoint/?next=instagram%3A%2F%2Fcheckpoint%2Fdismiss", $advSet);// prr($lrep, '');      
            $this->prcCK($lrep['cookies']); $ck = $this->ck;
           if (function_exists('nxs_getOption')) { $opVal = array(); $opNm = $this->opNm; $opVal = nxs_getOption($opNm); $opVal['ck'] = $ck; nxs_saveOption($opNm, $opVal); }      
           return 'cnf';
        } return 'IG M Error:'.print_r($rep, true);
    }
    function checkCode($url, $code){ $ck = $this->ck; foreach ($ck as $ci=>$cc) { if ( $ck[$ci]->name=='sessionid' && empty($ck[$ci]->value)) unset($ck[$ci]); }   foreach ($ck as $ci=>$cc) { if ( $ck[$ci]->name=='csrftoken') $tkn = $ck[$ci]->value; }        
      $flds = 'security_code='.$code; $hdrsArr = nxs_makeHeaders('https://www.instagram.com/','https://www.instagram.com/', $flds, true);  $hdrsArr['X-Instagram-AJAX'] = '1'; $hdrsArr['X-CSRFToken'] = $tkn;
      $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $flds, $this->proxy); $rep = nxs_remote_post('https://www.instagram.com'.$url, $advSet); //prr('https://www.instagram.com'.$url, 'Code CHECK'); prr($advSet); prr($rep);    
      $obj = @json_decode($rep['body'], true);  if ($obj['status']=='ok') { 
          $this->prcCK($rep['cookies']); $ck = $this->ck;  return $ck;
      } return false;
    }
    function post($msg, $imgURL, $style='E'){ $ck = $this->ck; if ($this->debug) echo "[IG] Posting to ...".$imgURL."<br/>\r\n"; $badOut = '';  $msg = nsTrnc(strip_tags($msg), 2200); 
      //## Get image
      $remImgURL = urldecode($imgURL); $urlParced = pathinfo($remImgURL); $remImgURLFilename = $urlParced['basename']; $imgType = substr(  $remImgURL, strrpos( $remImgURL , '.' )+1 ); if (stripos($imgType,'?')!==false) $imgType = @reset((explode('?', $imgType)));
      $hdrsArr = $this->headers($remImgURL); $nxAPIVer = defined('NXSAPIVER')?NXSAPIVER:'NV';  $nxAPIVer2 = defined('NextScripts_SNAP_Version')?NextScripts_SNAP_Version:'NV';
      $advSet = nxs_mkRemOptsArr($hdrsArr, '', '', $this->proxy); $imgData = nxs_remote_get($remImgURL, $advSet); // prr($remImgURL);  // prr($imgData);
      if(is_nxs_error($imgData) || empty($imgData['body']) || (!empty($imgData['headers']['content-length']) && (int)$imgData['headers']['content-length']<200) || 
          $imgData['headers']['content-type'] == 'text/html' ||  $imgData['response']['code'] == '403' ) { $options['attchImg'] = 0; 
           if (function_exists('nxs_addToLogN')) nxs_addToLogN('E','Error','IG','Could not get image ( '.$remImgURL.' ), will post without it - ', print_r($imgData, true)); return 'Image Upload Error, please see log';
      } $imgData = $imgData['body'];  $isJpg = stripos($imgType, 'jpg')!==false;
      if ($style=='C' || $style=='E' || !$isJpg) {
        $tmpX=array_search('uri', @array_flip(stream_get_meta_data($GLOBALS[mt_rand()]=tmpfile()))); 
        if (!is_writable($tmpX)) { $msg = "Can't upload image. Your temporary folder or file (file - ".$tmpX.") is not writable.";
          if (function_exists('wp_upload_dir')) { $uDir = wp_upload_dir(); $tmpX = tempnam($uDir['path'], "nx"); if (!is_writable($tmpX)) return $msg." Your UPLOADS folder or file (file - ".$tmpX.") is not writable. ";} else return $msg;
        } rename($tmpX, $tmpX.='.'.$imgType);  register_shutdown_function(create_function('', "@unlink('{$tmpX}');")); file_put_contents($tmpX, $imgData);
        $tmp=array_search('uri', @array_flip(stream_get_meta_data($GLOBALS[mt_rand()]=tmpfile()))); 
        if (!is_writable($tmp)) { $msg = "Can't upload image. Your temporary folder or file (file - ".$tmp.") is not writable.";
          if (function_exists('wp_upload_dir')) { $uDir = wp_upload_dir(); $tmp = tempnam($uDir['path'], "nx"); if (!is_writable($tmp)) return $msg." Your UPLOADS folder or file (file - ".$tmp.") is not writable. ";} else return $msg;
        } rename($tmp, $tmp.='.'.$imgType); register_shutdown_function(create_function('', "@unlink('{$tmp}');")); if(($style=='E' || $style=='C') && !function_exists('imagecreatefromjpeg')) { $badOut .= "GD is not available; Can't resize;\r\n<br/>"; $style='D'; }
        if (!empty($tmpX)) { if($style=='E') $this->makeSQExtend($tmpX, $tmp, 1080);  elseif ($style=='C') $this->makeSQCrop($tmpX, $tmp, 1080); else $this->makeJpg($tmpX, $tmp, 1080); }   $imgData = file_get_contents($tmp);
      } 
      foreach ($ck as $c) { if ($c->name=='csrftoken') $xftkn = $c->value;  if ($c->name=='ds_user_id') $uid = $c->value;} $ddt = date("Y:m:d H:i:s");      
      $octStreamArr = array(array('type' => 'form-data', 'name' => 'upload_id', 'data' => (time().'033')),array('type' => 'form-data','name' => 'photo','data' => $imgData,'filename' => 'pending_media_'.time().'003.jpg','headers' =>array("Content-type: image/jpeg")),array('type' => 'form-data', 'name' => 'media_type', 'data' => '1')); $data = $this->bldBody($octStreamArr);       
      $hdrsArr = $this->headers('https://www.instagram.com/create/style/', '', 'POST', true); $hdrsArr['Content-Type']= 'multipart/form-data; boundary='.str_replace('-','',$this->guid); $hdrsArr['X-Instagram-AJAX']= '1'; $hdrsArr['X-CSRFToken']= $this->tkn; $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $data, $this->proxy);// prr($hdrsArr); prr($ck); //prr($advSet);      
      $advSet['usearray'] = '1';  $rep = nxs_remote_post('https://www.instagram.com/create/upload/photo/', $advSet); //prr($rep); die();
      if (is_nxs_error($rep)) {  $badOut .= "|ERROR -02I- ".print_r($rep, true); return $badOut; }   
      if ($rep['response']['code']=='302' && stripos($rep['headers']['location'], 'accounts/login')!==falsee) { $c = new NXS_Http_Cookie( array('name' => 'lg', 'value' => 'lg')); $ck = array($c);
        $this->ck = $ck; if (function_exists('nxs_getOption')) { $opVal = array(); $opNm = $this->opNm; $opVal = nxs_getOption($opNm); $opVal['ck'] = $ck;   nxs_saveOption($opNm, $opVal); }  return "Logged out. Please try to make test post.";
      }
      if (empty($rep['body'])) {  $badOut .= "|ERROR -03I- ". print_r($rep, true); return $badOut; } $obj = @json_decode($rep['body'], true);
      if (($obj['status']!='ok' && $obj['message']=='login_required')) {   if ($this->debug) {  prr($obj); echo "[IG] Login M<br/>\r\n";}
         $res = $this->connectM(); if ($res=='cnf') return "You've got checkpoint! Please login to Instagram from your phone and confirm the login or action before trying to post again."; $ck = $this->ck;
         $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $data, $this->proxy); $rep = nxs_remote_post('https://i.instagram.com/api/v1/upload/photo/', $advSet);  $obj = @json_decode($rep['body'], true);
      }      
      if ($obj['status']!='ok' && stripos($obj['redirect_url'], '/accounts/login/')!==false) { $c = new NXS_Http_Cookie( array('name' => 'lg', 'value' => 'lg')); $ck = array($c);
        $this->ck = $ck; if (function_exists('nxs_getOption')) { $opVal = array(); $opNm = $this->opNm; $opVal = nxs_getOption($opNm); $opVal['ck'] = $ck;   nxs_saveOption($opNm, $opVal); }  
        return "Logged out. Please try to post again";
      }      
      if (empty($obj) || !is_array($obj) || empty($obj['status']) || $obj['status']!='ok'){ $badOut .= "|ERROR 04IG (".$nxAPIVer." [".$nxAPIVer2."]) - ".print_r($rep, true); return $badOut; }       
      
      $hdrsArr = $this->headers('https://www.instagram.com/create/details/', '', 'POST', true); $data = 'upload_id='.$obj['upload_id'].'&caption='.$msg;
      $hdrsArr['X-Instagram-AJAX']= '1'; $hdrsArr['X-CSRFToken']= $this->tkn; $hdrsArr['Content-Type']= 'application/x-www-form-urlencoded'; $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $data, $this->proxy);//prr($advSet);      
      $advSet['usearray'] = '1';  $rep = nxs_remote_post('https://www.instagram.com/create/configure/', $advSet);      
      if (is_nxs_error($rep)) {  $badOut .= "|ERROR -07I-".print_r($rep, true); return $badOut; }    if (empty($rep['body'])) {  $badOut .= "|ERROR -08I-".print_r($rep, true); return $badOut; }
      $obj = @json_decode($rep['body'], true);
      if (empty($obj) || !is_array($obj) || empty($obj['status'])){ $badOut .= "|ERROR -09I- ".print_r($rep, true).' | <br/> DATA:'.print_r($data, true); return $badOut; }      
      if ($obj['status']!='ok' && $obj['message']=='checkpoint_required') { return "You got checkpoint! Please login to Instagram from your phone and confirm the login or action."; } 
      if ($obj['status']!='ok' && !empty($obj['message'])) { $badOut .= "|ERROR -POST- ".print_r($obj, true); return $badOut; }
      if ($obj['status']=='ok') { return array("isPosted"=>"1", "postID"=>$obj['media']['code'], 'pDate'=>date('Y-m-d H:i:s'), "postURL"=>'https://www.instagram.com/p/'.$obj['media']['code'], 'msg'=>$badOut); } else {  $badOut .= print_r($rep, true)."|ERROR -XI- "; return $badOut; }
    }    
}}
//================================XING==========================================
if (!class_exists('nxsAPI_XI')){class nxsAPI_XI{ var $ck = array();  var $debug = false; var $proxy = array();    
    function createFile($imgURL) {
      $remImgURL = urldecode($imgURL); $urlParced = pathinfo($remImgURL); $remImgURLFilename = $urlParced['basename']; 
      $imgData = wp_remote_get($remImgURL, array('timeout' => 45)); if (is_nxs_error($imgData)) { $badOut['Error'] = print_r($imgData, true)." - ERROR"; return $badOut; }          
      if (isset($imgData['content-type'])) $cType = $imgData['content-type']; $imgData = $imgData['body']; if (empty($cType)) $cType = 'image/png';
      $tmp=array_search('uri', @array_flip(stream_get_meta_data($GLOBALS[mt_rand()]=tmpfile())));  
      if (!is_writable($tmp)) { $msg = "Can't upload image. Your temporary folder or file (file - ".$tmp.") is not writable.";
        if (function_exists('wp_upload_dir')) { $uDir = wp_upload_dir(); $tmp = tempnam($uDir['path'], "nx"); if (!is_writable($tmp)) return $msg." Your UPLOADS folder or file (file - ".$tmp.") is not writable. ";} else return $msg;
      } rename($tmp, $tmp.='.png'); register_shutdown_function(create_function('', "unlink('{$tmp}');"));       
      file_put_contents($tmp, $imgData); if (!$tmp) { $badOut['Error'] = 'You must specify a path to a file'; return $badOut; }  
      if (!file_exists($tmp)) { $badOut['Error'] = 'File path specified does not exist'; return $badOut; } 
      if (!is_readable($tmp)) { $badOut['Error'] = 'File path specified is not readable'; return $badOut; }
      $cfile = curl_file_create($tmp,$cType,'nxstmp.png'); return $cfile;      
    }    
    
    function check($u=''){ $ck = $this->ck;  if (!empty($ck) && is_array($ck)) { $hdrsArr = nxs_makeHeaders('https://www.xing.com'); if ($this->debug) echo "[XI] Checking....;<br/>\r\n"; 
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get('https://www.xing.com/app/settings?op=notifications', $advSet);// prr($rep);
        if (is_nxs_error($rep)) return false; $ck = $rep['cookies']; $contents = $rep['body']; //if ($this->debug) prr($contents);
        $ret = stripos($contents, '"/login/logout"')!==false; $usr = CutFromTo($contents, ' <strong>', '</strong>'); if ($ret & $this->debug) echo "[XI] Logged as:".$usr."<br/>\r\n"; 
        if (empty($u) || $u==$usr) return $ret; else return false;
      } else return false;
    }
    function connect($u,$p){ $badOut = 'Connect Error: ';
        //## Check if alrady IN
        if (!$this->check()){ if ($this->debug) echo "[XI] NO Saved Data;<br/>\r\n";  
        $hdrsArr = nxs_makeHeaders('https://www.xing.com'); $advSet = nxs_mkRemOptsArr($hdrsArr, '', '', $this->proxy); $rep = nxs_remote_get('https://www.xing.com', $advSet); // prr($rep);
        if (is_nxs_error($rep)) {  $badOut = "AUTH ERROR #1". print_r($rep, true); return $badOut; } $ck = $rep['cookies']; $contents = $rep['body']; if (!empty($this->proxy)) { $prx = explode(':',$this->proxy['proxy']); $this->proxy = $prx; }
        //## GET HIDDEN FIELDS
        $md = array(); $flds  = array(); $contents = trim(CutFromTo($contents,'action="https://login.xing.com/login" ', '</form'));
        while (stripos($contents, '<input')!==false){ $inpField = trim(CutFromTo($contents,'<input', '>')); $name = trim(CutFromTo($inpField,'name="', '"'));
          if ( stripos($inpField, '"hidden"')!==false && $name!='' && !in_array($name, $md)) { $md[] = $name; $val = trim(CutFromTo($inpField,'value="', '"')); $flds[$name]= $val; }
          $contents = substr($contents, stripos($contents, '<input')+8);
        } $flds['login_form[username]'] = $u; $flds['login_form[password]'] = $p;  $flds['login_form[perm]'] = '1'; 
        //## ACTUAL LOGIN 
        $hdrsArr = nxs_makeHeaders('https://www.xing.com/', 'https://www.xing.com', 'POST'); $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $flds, $this->proxy); //prr($advSet);
        $rep = nxs_remote_post('https://login.xing.com/login', $advSet);if (is_nxs_error($rep)) {  $badOut = "AUTH ERROR #2". print_r($rep, true); return $badOut; } // prr($rep); 
        if ($rep['response']['code']=='200') { if (stripos( $rep['body'], 'action="https://login.xing.com/login')!==false) { $contents = trim(CutFromTo($contents,'action="https://login.xing.com/login" ', '</form'));
            if (stripos( $rep['body'], 'app-message app-message-error')!==false) return CutFromTo($contents,'app-message app-message-error">', '</div');
          } else return "Error (Login): ".print_r($rep, true);        
        } elseif ($rep['response']['code']=='302' && stripos( $rep['body'], 'auth_token=')!==false) { $ck = nxs_MergeCookieArr($ck,  $rep['cookies']); $ck = nxsDelCookie($ck, 'login_session');
          $rURL = CutFromTo($rep['body'], 'href="', '"'); $hdrsArr = nxs_makeHeaders('https://www.xing.com/', 'https://www.xing.com'); $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy);  $rep = nxs_remote_get($rURL, $advSet); // prr($rep,$rURL);
          if ($rep['response']['code']=='302' && ( stripos( $rep['body'], 'app/user')!==false || stripos( $rep['body'], 'app/startpage')!==false )) {  $ckx = $rep['cookies']; $ckx[0]->value = urlencode($ckx[0]->value); $ck = nxs_MergeCookieArr($ckx,  $ck); // $ck = nxsDelCookie($ck, 'login_session');
          if ($this->debug) echo "[XI] Login was OK;<br/>\r\n"; $this->ck = $ck; return false; } else return "Error (Login #2): ".print_r($rep, true);              
        }
      } else { if ($this->debug) echo "[XI] Saved Data is OK;<br/>\r\n"; return false; }
    }
    function getPgsList($pgID){ $ck = $this->ck; $pgs = ''; if (!empty($ck) && is_array($ck)) { $hdrsArr = nxs_makeHeaders('https://www.xing.com'); if ($this->debug) echo "[XI] PG List....;<br/>\r\n"; 
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get('https://www.xing.com/companies/my_companies', $advSet);// prr($advSet); prr($rep);        
        if (is_nxs_error($rep)) return false; $ck = $rep['cookies']; $contents = $rep['body']; if (stripos($contents, 'data-sub-tab-content="companies"')!==false) { 
          $ct = CutFromTo($contents, 'data-sub-tab-content="companies"','</section>'); $cts = explode('<article',$ct); 
          foreach ($cts as $c) if (stripos($c, 'company-link')!==false) { $n = trim(CutFromTo($c,'<h1>','</h1>')); $id = CutFromTo($c,'href="/companies/','"'); $n = strip_tags($n); $pgs .= '<option class="nxsBlue" '.(($pgID==$n)?'selected="selected"':'').' value="'.$id.'">'.$n.' ('.$id.')</option>'; }
        } 
    } return $pgs; }
    function getGrpList($pgID){ $ck = $this->ck; $pgs = ''; if (!empty($ck) && is_array($ck)) { $hdrsArr = nxs_makeHeaders('https://www.xing.com'); if ($this->debug) echo "[XI] GRP List....;<br/>\r\n"; //https://www.xing.com/communities
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get('https://www.xing.com/communities', $advSet);// prr($advSet); prr($rep);        
        if (is_nxs_error($rep)) return false; $ck = $rep['cookies']; $contents = $rep['body']; if (stripos($contents, 'class="my-groups-list"')!==false) { 
          $ct = CutFromTo($contents, 'class="my-groups-list"','</section>'); $cts = explode('<li class="my-groups-list__item"',$ct); 
          foreach ($cts as $c) if (stripos($c, 'href="/communities/groups/')!==false) { $n = trim(strip_tags('<h4'.CutFromTo($c,'<h4','</h4>'))); $id =  CutFromTo($c,'href="/communities/groups/','"'); $n = strip_tags($n); $pgs .= '<option class="nxsGreen" '.(($pgID==$n)?'selected="selected"':'').' value="'.$id.'">'.$n.' ('.$id.')</option>'; }
        } 
    } return $pgs; }
    
    function getGrpForums($url, $pgID){ $ck = $this->ck; $pgs = ''; if (!empty($ck) && is_array($ck)) { $hdrsArr = nxs_makeHeaders('https://www.xing.com'); if ($this->debug) echo "[XI] Getting Forums List from ".$url."<br/>\r\n"; //https://www.xing.com/communities
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy,1); $rep = nxs_remote_get($url, $advSet); // prr($advSet); prr($rep);        
        if (is_nxs_error($rep)) return false; $ck = $rep['cookies']; $contents = $rep['body']; if (stripos($contents, '<ul class="comm-navigation-dropdown')!==false) { 
          $ct = CutFromTo($contents, '<ul class="comm-navigation-dropdown','</ul>');$cts = explode('<li class="comm-navigation-item',$ct); 
          foreach ($cts as $c) if (stripos($c, 'href="/communities/forums/')!==false) { $n = trim(urldecode(CutFromTo($c,'data-forum-name="','"'))); $id =  CutFromTo($c,'href="/communities/forums/','"'); $n = strip_tags($n); $pgs .= '<option class="nxsGreen" '.(($pgID==$n)?'selected="selected"':'').' value="'.$id.'">'.$n.' ('.$id.')</option>'; }
        } 
    } return $pgs; }
    function post($msg, $lnk){ global $nxs_plurl;  $ck = $this->ck; $pgs = ''; if (!empty($ck) && is_array($ck)) { $hdrsArr = nxs_makeHeaders('https://www.xing.com'); if ($this->debug) echo "[XI] Posting to PR<br/>\r\n"; //https://www.xing.com/communities
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy,1); $rep = nxs_remote_get('https://www.xing.com/app/startpage', $advSet); if (is_nxs_error($rep)) return 'Bad connection #1'; 
        $contents = $rep['body']; if (stripos($contents, 'csrfToken: "')!==false) $ct = CutFromTo($contents, 'csrfToken: "','"'); else return 'Bad connection #2: No Token';
        $flds = array('op'=>'share_message.save','sid'=>$ct, 'url'=>'','status'=>$msg,'tab'=>'status');  $hdrsArr = nxs_makeHeaders('https://www.xing.com','','POST', true);
        if (!empty($lnk)) { $hdrsArr = nxs_makeHeaders('https://www.xing.com','','GET',true); $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy,1); 
          $rep = nxs_remote_get('https://www.xing.com/app/share?op=get_preview&url='.urlencode($lnk).'&_='.time().'138', $advSet); if (!is_nxs_error($rep)) {
            $c = $rep['body']; if (stripos($c, '=\"link_id\" value=\"')!==false) $n = trim(CutFromTo($c,'=\"link_id\" value=\"','\"'));  $flds['tab'] = 'link'; $flds['url'] = $lnk; $flds['link_id'] = $n;            
          }
        } $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $flds, $this->proxy); $rep = nxs_remote_post('https://www.xing.com/app/share', $advSet); 
        if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR"; return $badOut; } $contents = $rep['body']; // prr($pURL); prr($advSet);  prr($rep, 'POST RESULT'); // die();
        if ($this->debug) echo "[XI] ACT Posting to PR<br/>\r\n";
        if (stripos($contents, '{"success":1')!==false ) { $hdrsArr = nxs_makeHeaders('https://www.xing.com/app/startpage','','GET',true);
          $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rURL = 'https://www.xing.com/feedy/network_feed?_='.time().'526'; $rep = nxs_remote_get($rURL, $advSet); $contents = $rep['body'];            
            $pid = CutFromTo($contents,' data-path="/feedy/stories/','"');  $to = 'https://www.xing.com/feedy/stories/'.$pid;
          return array('isPosted'=>'1', 'postID'=>$pid, 'postURL'=>$to, 'pDate'=>date('Y-m-d H:i:s'), 'ck'=>$ck);
        }    
    }}    
    function postG($msg, $msgT, $grpID, $forumID, $imgURL){ global $nxs_plurl;  $ck = $this->ck; $pgs = ''; if (!empty($ck) && is_array($ck)) { 
        $curl = 'https://www.xing.com/communities/groups/'.$grpID;  $hdrsArr = nxs_makeHeaders($curl, 'https://www.xing.com'); if ($this->debug) echo "[XI] Posting to G<br/>\r\n"; 
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy,1); $rep = nxs_remote_get($curl, $advSet); if (is_nxs_error($rep)) return 'Bad connection #1'; 
        $contents = $rep['body']; if (stripos($contents, 'csrf-token" content="')!==false) $ct = CutFromTo($contents, 'csrf-token" content="','"'); else return 'Bad connection #2: No Token';
        $contents = $rep['body']; if (stripos($contents, 'authenticity_token" value="')!==false) $cta = CutFromTo($contents, 'authenticity_token" value="','"'); else return 'Bad connection #4: No AU Token';
        $flds = array('utf8'=>urldecode('%E2%9C%93'),'authenticity_token'=>$cta, 'post[title]'=>$msgT,'post[content]'=>$msg,'post[image_attributes][slug]'=>'','post[forum_id]'=>$forumID,'post[media_preview_id]'=>'','delete_image'=>'','X-represents-preview-image'=>'','stream'=>'true');          
        if (!empty($imgURL)){ $pstArray = array('X-Requested-With'=>'IFrame','authenticity_token'=>$cta);// prr($imgURL);
          $imRes = nxs_uploadImgCD($imgURL, 'https://www.xing.com/communities/upload/content_images.json', $pstArray, 'image', $ck);// prr($imRes);
          if (is_array($imRes) && !empty($imRes['body'])){ $cc = json_decode($imRes['body'], true); if (!empty($cc['image'])) { 
            $hdrsArr = nxs_makeHeaders($curl, 'https://www.xing.com','GET', true); $hdrsArr['X-CSRF-Token'] = $ct; $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy);            
            while ($cc['image']['processing']==true) { sleep(2); $rep = nxs_remote_get('https://www.xing.com'.$cc['image']['url'], $advSet); $cc = json_decode($rep['body'], true); // prr($rep); prr($cc, 'CCCC');
              if ($cc['image']['processing']==false && !empty($cc['image']['main_url'])) { $flds['post[image_attributes][slug]'] = $cc['image']['slug']; $flds['X-represents-preview-image'] = $cc['image']['main_url']; } else $badOut = 'ImgError'; 
        }}}} $hdrsArr = nxs_makeHeaders($curl, 'https://www.xing.com','POST', true); $hdrsArr['X-CSRF-Token'] = $ct; $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $flds, $this->proxy); //prr($advSet);// die();
        $rep = nxs_remote_post('https://www.xing.com/communities/forums/'.$forumID.'/posts.json', $advSet); 
        if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR"; return $badOut; } $contents = $rep['body']; // prr($advSet);  prr($rep, 'POST RESULT'); // die();
        if ($this->debug) echo "[XI] ACT Posting to G<br/>\r\n";
        if (stripos($contents, '{"success":true')!==false ) return array('isPosted'=>'1', 'postID'=>CutFromTo($contents,'id=\"','\"'), 'postURL'=>'https://www.xing.com'.CutFromTo($contents,'data-comments-path=\"','\"'), 'pDate'=>date('Y-m-d H:i:s'), 'ck'=>$ck);
    }}  
    function postC($msg, $msgT, $pgID){ global $nxs_plurl;  $ck = $this->ck; $pgs = ''; $curl = 'https://www.xing.com/companies/'.$pgID.'/updates'; if (!empty($ck) && is_array($ck)) { $hdrsArr = nxs_makeHeaders($curl, 'https://www.xing.com'); if ($this->debug) echo "[XI] Posting to CP<br/>\r\n"; 
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy,1); $rep = nxs_remote_get($curl, $advSet); if (is_nxs_error($rep)) return 'Bad connection #1'; 
        $contents = $rep['body']; if (stripos($contents, 'csrf-token" content="')!==false) $ct = CutFromTo($contents, 'csrf-token" content="','"'); else return 'Bad connection #2: No Token'; $hdrsArr = nxs_makeHeaders($curl, 'https://www.xing.com','GET',true); $hdrsArr['X-CSRF-Token'] = $ct;
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get($curl.'/new?_='.time().'516', $advSet); if (is_nxs_error($rep)) return 'Bad connection #3';
        $contents = $rep['body']; if (stripos($contents, 'authenticity_token" value="')!==false) $cta = CutFromTo($contents, 'authenticity_token" value="','"'); else return 'Bad connection #4: No AU Token';
        $flds = array('utf8'=>urldecode('%E2%9C%93'),'authenticity_token'=>$cta, 'update[headline]'=>$msgT,'update[body_clean]'=>$msg,'update[publish_to_twitter]'=>'0');  $hdrsArr = nxs_makeHeaders($curl, 'https://www.xing.com','POST', true); $hdrsArr['X-CSRF-Token'] = $ct; 
        $flds = http_build_query($flds); //prr($flds);
        //## Post
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $flds, $this->proxy); $rep = nxs_remote_post($curl, $advSet); 
        if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR"; return $badOut; } $contents = $rep['body']; // prr($pURL); prr($advSet);  prr($rep, 'POST RESULT'); // die();
        if ($this->debug) echo "[XI] ACT Posting to C<br/>\r\n";
        if (stripos($contents, 'data-status="success"')!==false ) { return array('isPosted'=>'1', 'postID'=>'0', 'postURL'=>$curl, 'pDate'=>date('Y-m-d H:i:s'));} else return 'Error: '. print_r($rep, true);    
    }}   
} }
//================================Scoop.It==========================================
if (!class_exists('nxsAPI_SC')){class nxsAPI_SC{ var $ck = array();  var $debug = false; var $proxy = array(); var $u=''; var $p=''; var $t = '';    
    function check($u=''){ $ck = $this->ck;  if (!empty($ck) && is_array($ck)) { $hdrsArr = nxs_makeHeaders('https://www.scoop.it'); if ($this->debug) echo "[SC] Checking....;<br/>\r\n"; 
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get('https://www.scoop.it/bookmarkletInfo', $advSet);// prr($rep);
        if (is_nxs_error($rep)) return false; $ck = $rep['cookies']; $contents = $rep['body']; //if ($this->debug) prr($contents);
        $ret = stripos($contents, "'/logout'")!==false; $usr = CutFromTo($contents, "href='/u/", "'"); if ($ret & $this->debug) echo "[SC] Logged as:".$usr."<br/>\r\n"; 
        if (empty($u) || $u==$usr) return $ret; else return false;
      } else return false;
    }
    function connect($u='',$p=''){ $badOut = 'Connect Error: '; if (!empty($u)) $this->u = $u; if (!empty($p)) $this->p = $p; $u = $this->u; $p = $this->p; //## Check if alrady IN
        if (!$this->check()){ if ($this->debug) echo "[SC] NO Saved Data;<br/>\r\n";  
        $hdrsArr = nxs_makeHeaders('https://www.scoop.it'); $advSet = nxs_mkRemOptsArr($hdrsArr, '', '', $this->proxy); $rep = nxs_remote_get('https://www.scoop.it/login', $advSet); // prr($rep);
        if (is_nxs_error($rep)) {  $badOut = "AUTH ERROR #1". print_r($rep, true); return $badOut; } $ck = $rep['cookies']; $contents = $rep['body']; if (!empty($this->proxy)) { $prx = explode(':',$this->proxy['proxy']); $this->proxy = $prx; }
        //## ACTUAL LOGIN 
        $flds = 'email='.urlencode($u).'&password='.urlencode($p).'&rememberMe=true';
        $hdrsArr = nxs_makeHeaders('https://www.scoop.it/login', 'https://www.scoop.it', 'POST'); $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $flds, $this->proxy); //prr($advSet);
        $rep = nxs_remote_post('https://www.scoop.it/login?redirectUrl=%2F', $advSet);if (is_nxs_error($rep)) {  $badOut = "AUTH ERROR #2". print_r($rep, true); return $badOut; } // prr($rep);  die();
        if ($rep['response']['code']=='200') { if (stripos( $rep['body'], ' alert-error')!==false) { $contents = trim(CutFromTo($rep['body'],' alert-error">', '</div')); return 'Login Error: '.$contents; } else return "Error (Login): ".print_r($rep, true);        
        } elseif ($rep['response']['code']=='302' && ( stripos( $rep['headers']['set-cookie'], 'auth=')!==false )) { $ck = nxs_MergeCookieArr($ck,  $rep['cookies']);           
          if ($this->debug) echo "[SC] Login was OK;<br/>\r\n"; $this->ck = $ck; return false; } else return "Error (Login #2): ".print_r($rep, true);                      
      } else { if ($this->debug) echo "[SC] Saved Data is OK;<br/>\r\n"; return false; }
    }    
    function post($msg, $lnk, $imgURL, $title, $ucnt, $tags){ global $nxs_plurl; $t = $this->t; if (empty($t)) $t = 'default'; $ck = $this->ck; $pgs = ''; 
      if (!empty($ck) && is_array($ck)) { $hdrsArr = nxs_makeHeaders('https://www.scoop.it'); if ($this->debug) echo "[SC] Ready to post to topic '".$t."'<br/>\r\n"; 
        if (!empty($lnk)) { $gURL = 'https://www.scoop.it/t/'.$t.'/p/create/2?inlineMode=1&scoopitWindowId=inline-scoopitWindow-1949c6c0-0617-11e8-ccff-90b11c3d2b20&themeLid=2726116&suggestionEngineLid=2182681&urlToResolve='.$lnk.'&urlChooserPostWithoutUrl=0&isAjax=true&hitTheUrlChooserButton=true';
          $cu = ''; $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy,1); $rep = nxs_remote_get($gURL, $advSet); if (is_nxs_error($rep)) return 'Bad connection #1'; 
          $contents = $rep['body']; if (stripos($contents, '{"isHulk":')!==false) { $jc = json_decode($contents,true); if (!empty($jc) && is_array($jc) && !empty($jc['post']) && !empty($jc['post']['cryptoUrl'])) $cu = $jc['post']['cryptoUrl']; }
        }
        $flds = array('facebook-share-content'=>'','twitter-share-content'=>'','linkedin-share-content'=>'','action'=>'create','title'=>$title,'content'=>$msg,'userCurationContent'=>$ucnt,'themeLid'=>'2726116','source_lid'=>'67219170','imageStyleSize'=>'big','imageStylePosition'=>'center','scoopitWindowId'=>'h_scoopitWindowPopup','isAjax'=>'true'); if (!empty($tags)) $flds['item']['tags'] = $tags; if (!empty($imgURL)) { $flds['imageUrl'] = $imgURL; $flds['imageUrls'] = $imgURL; } if (!empty($cu)) $flds['url'] = $cu;  $flds = http_build_query($flds); 
        $hdrsArr = nxs_makeHeaders('https://www.scoop.it/t/'.$t.'/','https://www.scoop.it','POST', true); if ($this->debug) echo "[SC] Posting to ".'https://www.scoop.it/t/'.$t.'/p/create/2'."<br/>\r\n";        
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $flds, $this->proxy); $rep = nxs_remote_post('https://www.scoop.it/t/'.$t.'/p/create/2', $advSet); $contents = $rep['body'];        
        if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR"; return $badOut; } if ($rep['response']['code']=='404') return 'Error: Topic NOT FOUND. Please make sure "Topic URL" is correct - https://www.scoop.it/t/'.$t;
        $contents = $rep['body']; // prr($pURL); prr($advSet);  prr($rep, 'POST RESULT'); // die();        
        if (stripos($contents, 'ajax/refreshPosts\", \"')!==false ) { $pid = CutFromTo($contents,'ajax/refreshPosts\", \"', '\"'); $pURL = 'https://www.scoop.it/t/'.$t.'/p/'.$pid.'/';            
          return array('isPosted'=>'1', 'postID'=>$pid, 'postURL'=>$pURL, 'pDate'=>date('Y-m-d H:i:s'), 'ck'=>$ck);
        } elseif (stripos($contents, 'scoopitWindow-success-content-explain')!==false ) { $pid = CutFromTo($contents,'/p/', '/'); $x = CutFromTo($contents,'subButton', "target='_blank'"); $pURL = 'https://www.scoop.it/'.CutFromTo($x,"href='", "'");            
          return array('isPosted'=>'1', 'postID'=>$pid, 'postURL'=>$pURL, 'pDate'=>date('Y-m-d H:i:s'), 'ck'=>$ck);
        } 
        
        else return "POST ERROR CS ".print_r($rep, true);   
    }}        
} }
?>