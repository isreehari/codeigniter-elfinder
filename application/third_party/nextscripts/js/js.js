(function($) {
  $(function() {
	 jQuery('#nxs_snapAddNew').bind('click', function(e) { e.preventDefault(); jQuery('#nxs_spPopup').bPopup({ modalClose: false, appendTo: '#nsStForm', opacity: 0.6, follow: [false, false], position: [65, 50]}); });     
  });
})(jQuery);

function doDelAcct(nt, blID, blName){  var answer = confirm("Remove "+blName+" account?");
  if (answer){ var data = { action:'nxs_snap_aj',  nxsact: 'nsDN', id: 0, nt: nt, id: blID, _wpnonce: jQuery('input#nxsSsPageWPN_wpnonce').val()}; 
	jQuery.post('snap-setup.php', data, function(response) {  window.location = window.location.href.split("#")[0]; });
  }           
}      

function nxs_svSet(nt,ii,rel) {  jQuery("#nxsAllAccntsDiv").addClass("loading");  
	//## jQuery clone fix for empty textareas
	(function (original) { jQuery.fn.clone = function () { var result = original.apply(this, arguments),
	  my_textareas = this.find('textarea').add(this.filter('textarea')), result_textareas = result.find('textarea').add(result.filter('textarea')), 
	  my_selects = this.find('select').add(this.filter('select')), result_selects = result.find('select').add(result.filter('select'));
	  for (var i = 0, l = my_textareas.length; i < l; ++i) jQuery(result_textareas[i]).val( jQuery(my_textareas[i]).val() );    
	  for (var i = 0, l = my_selects.length; i < l; ++i) for (var j = 0, m = my_selects[i].options.length; j < m; ++j) if (my_selects[i].options[j].selected === true) result_selects[i].options[j].selected = true;    
	  return result;
	}; }) (jQuery.fn.clone);    
	
	var elID = ''; if (rel!='') elID = 'dom'+nt.toUpperCase()+ii+'Div'; else elID = 'nxsAllAccntsDiv'; jQuery("#nxsSaveLoadingImg"+nt+ii).show(); 
	if (jQuery("#nxsSettingsDiv").length) { jQuery("#nxsAllAccntsDiv").addClass("loading");  
	  if (jQuery("#nxsAllAccntsDiv").length) jQuery("#nxsSettingsDiv").appendTo("#nxsAllAccntsDiv"); else  elID = 'nxsSettingsDiv'; 
	} alert(elID);
	var frmTxt = '<div id="nxs_tmpDiv_'+nt+ii+'" style="display:none;"><form id="nxs_tmpFrm_'+nt+ii+'"><input name="action" value="nxs_snap_aj" type="hidden" /><input name="nxsact" value="setNTset" type="hidden" /><input name="nxs_mqTest" value="\'" type="hidden" /><input type="hidden" name="_wp_http_referer" value="'+jQuery("input[name='_wp_http_referer']").val()+'" /><input type="hidden" name="_wpnonce" value="'+jQuery('#nxsSsPageWPN_wpnonce').val()+'" /></form></div>';
	jQuery("body").append(frmTxt); jQuery("#"+elID).clone(true).appendTo("#nxs_tmpFrm_"+nt+ii); var serTxt = jQuery("#nxs_tmpFrm_"+nt+ii).serialize(); jQuery("#nxs_tmpDiv_"+nt+ii).remove();// alert(serTxt);
	jQuery.ajax({ type: "POST", url: ajaxurl, data: serTxt, 
	  success: function(data){ jQuery("#nxsAllAccntsDiv").removeClass("loading"); if (data=='OK') { jQuery("#doneMsg"+nt+ii).show(); jQuery("#nxsSaveLoadingImg"+nt+ii).hide(); jQuery("#doneMsg"+nt+ii).delay(600).fadeOut(3200); if (rel!='') window.location = jQuery(location).attr('href'); }}
	});
}

function testPost(nt, nid){ jQuery('#nxs_cntPopup').bPopup({ contentContainer:'.nxsAJcnt', content:'ajax', loadUrl: 'snap-setup.php', loadData:{action:'nxs_snap_aj',nxsact:'tst',nt:nt,nid:nid}, modalClose: false, opacity: 0.6,  positionStyle: 'fixed', onOpen: function() { jQuery('.nxs_pppSpinner').show(); }, loadCallback: function() { jQuery('.nxs_pppSpinner').hide(); } });
	 
}

function doGetHideNTBlock(bl,ii){ 
   if (jQuery('#apDoS'+bl+ii).length<1 || jQuery('#apDoS'+bl+ii).val()=='0') { 
	if (jQuery('#do'+bl+ii+'Div').length<1) {  jQuery("#"+bl+ii+"LoadingImg").show();
	  jQuery.post(ajaxurl,{nxsact:'getNTset',nt:bl,ii:ii,action:'nxs_snap_aj', _wpnonce: jQuery('input#nxsSsPageWPN_wpnonce').val()}, function(j){ var options = '';        
		//## Show data
		if (ii=='N') { 
		  jQuery('#nsx_addNT').html(j);  nxs_doTabsInd('#nsx_addNT'); jQuery('#nsx_addNT > div:first-child').show(); jQuery('#nsx_addNT > div:first-child > div:first-child').show();
		} else { 
		  jQuery('#nxsNTSetDiv'+bl+ii).html(j); nxs_doTabsInd('#nxsNTSetDiv'+bl+ii); jQuery("#"+bl+ii+"LoadingImg").hide(); jQuery('#do'+bl+ii+'Div').show(); jQuery('#do'+bl+ii+'AG').text('[Hide Settings]'); jQuery('#apDoS'+bl+ii).val('1');        
		  if (jQuery('#rbtn'+bl.toLowerCase()+ii).attr('type') != 'checkbox') jQuery('#rbtn'+bl.toLowerCase()+ii).attr('type', 'checkbox');          
		}
		// if (filtersReset) jQuery('#catSelA'+bl+ii).prop('checked', true);
	  }, "html")
	} else { jQuery('#do'+bl+ii+'Div').show(); jQuery('#do'+bl+ii+'AG').text('[Hide Settings]'); jQuery('#apDoS'+bl+ii).val('1'); }
  } else { jQuery('#do'+bl+ii+'Div').hide(); jQuery('#do'+bl+ii+'AG').text('[Show Settings]'); jQuery('#apDoS'+bl+ii).val('0'); }  
}

function nxs_doTabsInd(iid){    
	//When page loads...
	jQuery(iid+" .nsx_tab_content").hide(); //Hide all content
	jQuery(iid+" ul.nsx_tabs > li:first-child").addClass("active").show(); //Activate first tab
	jQuery(iid+" .nsx_tab_container > .nsx_tab_content:first-child").show(); //Show first tab content

	//On Click Event
	jQuery(iid+" ul.nsx_tabs li").click(function() {
	  jQuery(this).parent().children("li").removeClass("active"); //Remove any "active" class
	  jQuery(this).addClass("active"); //Add "active" class to selected tab
	  jQuery(this).parent().parent().children(".nsx_tab_container").children(".nsx_tab_content").hide(); //Hide all tab content    
	  var activeTab = jQuery(this).find("a").attr("href"); //Find the href attribute value to identify the active tab + content
	  jQuery(activeTab).show(); //Fade in the active ID content
	  return false;
	});
	  
  }

function nxs_svSetAdv(nt,ii,divIn,divOut,loc,isModal){ jQuery(':focus').blur();
    //## jQuery clone fix for empty textareas
    (function (original) { jQuery.fn.clone = function () { var result = original.apply(this, arguments),
      my_textareas = this.find('textarea').add(this.filter('textarea')), result_textareas = result.find('textarea').add(result.filter('textarea')), 
      my_selects = this.find('select').add(this.filter('select')), result_selects = result.find('select').add(result.filter('select'));
      for (var i = 0, l = my_textareas.length; i < l; ++i) jQuery(result_textareas[i]).val( jQuery(my_textareas[i]).val() );    
      for (var i = 0, l = my_selects.length; i < l; ++i) for (var j = 0, m = my_selects[i].options.length; j < m; ++j) if (my_selects[i].options[j].selected === true) result_selects[i].options[j].selected = true;    
      return result;
    }; }) (jQuery.fn.clone); 
    //## /END jQuery clone fix for empty textareas
    if (isModal=='1') { jQuery("#"+divIn).addClass("loading");  jQuery("#nxsSaveLoadingImg"+nt+ii).show(); } else { jQuery("#"+nt+ii+"ldImg").show(); jQuery("#"+nt+ii+"rfrshImg").hide();   }
    if (divIn=='nxsAllAccntsDiv' && jQuery("#nxsAllAccntsDiv").length && jQuery("#nxsSettingsDiv").length) jQuery("#nxsSettingsDiv").appendTo("#nxsAllAccntsDiv");
    
    var isOut=''; if (typeof(divOut)!='undefined' && divOut!='') isOut = '<input type="hidden" name="isOut" value="1" />';
    
    frmTxt = '<div id="nxs_tmpDiv_'+nt+ii+'" style="display:none;"><form id="nxs_tmpFrm_'+nt+ii+'"><input name="action" value="nxs_snap_aj" type="hidden" /><input name="nxsact" value="setNTset" type="hidden" /><input name="nxs_mqTest" value="\'" type="hidden" /><input type="hidden" name="_wp_http_referer" value="'+jQuery("input[name='_wp_http_referer']").val()+'" /><input type="hidden" name="_wpnonce" value="'+jQuery('#nxsSsPageWPN_wpnonce').val()+'" />'+isOut+'</form></div>';
    jQuery("body").append(frmTxt); jQuery("#"+divIn).clone(true).appendTo("#nxs_tmpFrm_"+nt+ii); var serTxt = jQuery("#nxs_tmpFrm_"+nt+ii).serialize(); jQuery("#nxs_tmpDiv_"+nt+ii).remove();// alert(serTxt);
    jQuery.ajax({ type: "POST", url: ajaxurl, data: serTxt, 
      success: function(data){ if (isModal=='1') jQuery("#nxsAllAccntsDiv").removeClass("loading"); else {  jQuery("#"+nt+ii+"rfrshImg").show(); jQuery("#"+nt+ii+"ldImg").hide(); }
      if(typeof(divOut)!='undefined' && divOut!='' && data!='OK') jQuery('#'+divOut).html(data); 
      if (isModal=='1') {  jQuery("#nxsSaveLoadingImg"+nt+ii).hide(); jQuery("#doneMsg"+nt+ii).show(); jQuery("#doneMsg"+nt+ii).delay(600).fadeOut(3200); }
        if (loc!='') { if (loc!='r') window.location = loc; else window.location = jQuery(location).attr('href'); } 
      }
    });
    
}

///## API Specific Functions
//## GP
      function nxs_gpGetAllInfo(ii,force){ var u = jQuery('#apGPUName'+ii).val(); var p = jQuery('#apGPPass'+ii).val(); var pstAs = jQuery('#gpPostAs'+ii).val(); var pg = jQuery('#gpWhToPost'+ii).val(); jQuery("#gpPostAs"+ii).focus();
            jQuery('#gp'+ii+'rfrshImg').hide();  jQuery('#gp'+ii+'ldImg').show();
            jQuery.post(ajaxurl,{action: 'nxs_snap_aj',"nxsact":"getItFromNT", "fName":"getListOfPages", nt:"GP", u:u, p:p, ii:ii, pg:pg, pstAs:pstAs, force:force, isOut:1, nxs_mqTest:"'", _wpnonce: jQuery('#nxsSsPageWPN_wpnonce').val()}, function(j){  
                 if (j.indexOf('<option')>-1) { jQuery("#gpPostAs"+ii).html(j);  nxs_gpGetWhereToPost(ii,force); 
                   if (jQuery('#gpPostAs'+ii).val()!='a' && jQuery('#gpPostAs'+ii).val()!='p') jQuery('#gpPostAsNm'+ii).val(jQuery('#gpPostAs'+ii+' :selected').text()); else jQuery('#gpPostAsNm'+ii).val('p');
                 } else { jQuery("#nxsGPMsgDiv"+ii).html(j);   jQuery('#gp'+ii+'ldImg').hide();  jQuery('#gp'+ii+'rfrshImg').show(); }
            }, "html")          
      }
      function nxs_gpGetWhereToPost(ii,force){ var u = jQuery('#apGPUName'+ii).val(); var p = jQuery('#apGPPass'+ii).val(); var pstAs = jQuery('#gpPostAs'+ii).val(); var pg = jQuery('#gpWhToPost'+ii).val(); var pstAsNm = jQuery('#gpPostAs'+ii+' :selected').text();
            jQuery.post(ajaxurl,{action: 'nxs_snap_aj',"nxsact":"getItFromNT", "fName":"getListWhereToPost", nt:"GP", u:u, p:p, ii:ii, pg:pg, pstAs:pstAs, pstAsNm:pstAsNm, force:force, isOut:1, nxs_mqTest:"'", _wpnonce: jQuery('#nxsSsPageWPN_wpnonce').val()}, function(j){  
                 jQuery("#gpWhToPost"+ii).html(j);  nxs_gpGetCommCats(ii,force);
            }, "html")          
      }
      function nxs_gpGetCommCats(ii,force){ var u = jQuery('#apGPUName'+ii).val(); var p = jQuery('#apGPPass'+ii).val(); var pstAs = jQuery('#gpPostAs'+ii).val(); var pg = jQuery('#gpWhToPost'+ii).val();
         if (pg.charAt(0)=='c'){ 
            jQuery.post(ajaxurl,{action: 'nxs_snap_aj',"nxsact":"getItFromNT", "fName":"getGPCommInfo", nt:"GP", u:u, p:p, ii:ii, pg:pg, pstAs:pstAs, force:force, isOut:1, nxs_mqTest:"'", _wpnonce: jQuery('#nxsSsPageWPN_wpnonce').val()}, function(j){  
                 jQuery("#gpCommCat"+ii).html(j); jQuery('#nxsGPInfoDivComm'+ii).show();   jQuery('#gp'+ii+'ldImg').hide();  jQuery('#gp'+ii+'rfrshImg').show(); jQuery("#nxsGPMsgDiv"+ii).html("&nbsp;"); 
            }, "html")          
         } else { jQuery('#gp'+ii+'ldImg').hide();  jQuery('#gp'+ii+'rfrshImg').show(); jQuery("#nxsGPMsgDiv"+ii).html("&nbsp;");  }
      }
      function nxs_gpPostAsChange(ii, sObj){  if (sObj.val()!='a' && sObj.val()!='p') jQuery('#gpPostAsNm'+ii).val(jQuery('#gpPostAs'+ii+' :selected').text()); else jQuery('#gpPostAsNm'+ii).val('p');
          if (sObj.val()=='a'){ sObj.hide(); jQuery('#gpPstAsCst'+ii).show(); } 
            else { jQuery('#gp'+ii+'ldImg').show(); jQuery('#gp'+ii+'rfrshImg').hide(); nxs_gpGetWhereToPost(ii,0); }
      }      
      function nxs_gpWhToPostChange(ii, sObj){  jQuery('#nxsGPInfoDivComm'+ii).hide(); if (jQuery('#gpPostAs'+ii).val()!='a' && jQuery('#gpPostAs'+ii).val()!='p') jQuery('#gpPostAsNm'+ii).val(jQuery('#gpPostAs'+ii+' :selected').text()); else jQuery('#gpPostAsNm'+ii).val('p');
          if (sObj.val()=='a'){ sObj.hide(); jQuery('#gpPgIDcst'+ii).show(); }
          if (sObj.val().charAt(0)=='c'){ jQuery('#gp'+ii+'ldImg').show(); jQuery('#gp'+ii+'rfrshImg').hide();  var pg = sObj.val(); nxs_gpGetCommCats(ii,0); }
      }
//## RD
      function nxs_rdGetSRs(ii,force){ var u = jQuery('#apRDUName'+ii).val(); var p = jQuery('#apRDPass'+ii).val(); var rdSR = jQuery('#rdSubReddit'+ii).val(); jQuery("#rdSubReddit"+ii).focus();
            jQuery('#rd'+ii+'rfrshImg').hide();  jQuery('#rd'+ii+'ldImg').show(); jQuery("#nxsRDMsgDiv"+ii).html("&nbsp;"); jQuery("#rdSubReddit"+ii).html("<option value=\"\">Getting SubReddits.......</option>");
            jQuery.post(ajaxurl,{action: 'nxs_snap_aj',"nxsact":"getItFromNT", "fName":"getListOfSubReddits", nt:"RD", u:u, p:p, ii:ii, rdSR:rdSR, force:force, isOut:1, nxs_mqTest:"'", _wpnonce: jQuery('#nxsSsPageWPN_wpnonce').val()}, function(j){  
                 if (j.indexOf('<option')>-1) jQuery("#rdSubReddit"+ii).html(j); else jQuery("#nxsRDMsgDiv"+ii).html(j); jQuery('#rd'+ii+'ldImg').hide(); jQuery('#rd'+ii+'rfrshImg').show();
            }, "html")          
      }
      function nxs_rdSRChange(ii, sObj){  
          if (sObj.val()=='a'){ sObj.hide(); jQuery('#rdSRIDCst'+ii).show(); } 
      }                  
//## PN
      function nxs_pnGetBoards(ii,force){ var u = jQuery('#apPNUName'+ii).val(); var p = jQuery('#apPNPass'+ii).val(); var pnBoard = jQuery('#pnBoard'+ii).val(); jQuery("#pnBoard"+ii).focus();
            jQuery('#pn'+ii+'rfrshImg').hide();  jQuery('#pn'+ii+'ldImg').show(); jQuery("#nxsPNMsgDiv"+ii).html("&nbsp;"); jQuery("#pnBoard"+ii).html("<option value=\"\">Getting boards.......</option>");
            jQuery.post(ajaxurl,{action: 'nxs_snap_aj',"nxsact":"getItFromNT", "fName":"getListOfPNBoards", nt:"PN", u:u, p:p, ii:ii, pnBoard:pnBoard, force:force, isOut:1, nxs_mqTest:"'", _wpnonce: jQuery('#nxsSsPageWPN_wpnonce').val()}, function(j){  
                 if (j.indexOf('<option')>-1) jQuery("#pnBoard"+ii).html(j); else jQuery("#nxsPNMsgDiv"+ii).html(j); jQuery('#pn'+ii+'ldImg').hide(); jQuery('#pn'+ii+'rfrshImg').show();
            }, "html")          
      }
      function nxs_pnBoardChange(ii, sObj){  
          if (sObj.val()=='a'){ sObj.hide(); jQuery('#pnBRDIDCst'+ii).show(); } 
      }                  
//## TR
      function nxs_trGetBlogs(ii,force){ var u = jQuery('#trappKey'+ii).val(); var p = jQuery('#trAuthUser'+ii).val(); var cBlog = jQuery('#trpgID'+ii).val(); jQuery("#trpgID"+ii).focus();
            jQuery('#tr'+ii+'rfrshImg').hide();  jQuery('#tr'+ii+'ldImg').show(); jQuery("#nxsTRMsgDiv"+ii).html("&nbsp;"); jQuery("#trpgID"+ii).html("<option value=\"\">Getting Blogs.......</option>");
            jQuery.post(ajaxurl,{action: 'nxs_snap_aj',"nxsact":"getItFromNT", "fName":"getListOfBlogs", nt:"TR", u:u, p:p, ii:ii, cBlog:cBlog, force:force, isOut:1, nxs_mqTest:"'", _wpnonce: jQuery('#nxsSsPageWPN_wpnonce').val()}, function(j){  
                 if (j.indexOf('<option')>-1) jQuery("#trpgID"+ii).html(j); else jQuery("#nxsTRMsgDiv"+ii).html(j); jQuery('#tr'+ii+'ldImg').hide(); jQuery('#tr'+ii+'rfrshImg').show();
            }, "html")          
      }
      function nxs_trBlogChange(ii, sObj){  
          if (sObj.val()=='a'){ sObj.hide(); jQuery('#trInpCst'+ii).show(); } 
      }     
//## LI
      function nxs_liGetPages(ii,force){ var u = jQuery('#liappKey'+ii).val(); var p = jQuery('#liAuthUser'+ii).val(); var cBlog = jQuery('#lipgID'+ii).val(); jQuery("#lipgID"+ii).focus();
            jQuery('#li'+ii+'rfrshImg').hide();  jQuery('#li'+ii+'ldImg').show(); jQuery("#nxsLIMsgDiv"+ii).html("&nbsp;"); jQuery("#lipgID"+ii).html("<option value=\"\">Getting Pages.......</option>");
            jQuery.post(ajaxurl,{action: 'nxs_snap_aj',"nxsact":"getItFromNT", "fName":"getListOfPagesLIV2", nt:"LI", u:u, p:p, ii:ii, cBlog:cBlog, force:force, isOut:1, nxs_mqTest:"'", _wpnonce: jQuery('#nxsSsPageWPN_wpnonce').val()}, function(j){  
                 if (j.indexOf('<option')>-1) jQuery("#lipgID"+ii).html(j); else jQuery("#nxsLIMsgDiv"+ii).html(j); jQuery('#li'+ii+'ldImg').hide(); jQuery('#li'+ii+'rfrshImg').show();
            }, "html")          
      }      
      function nxs_liPageChange(ii, sObj){  
          if (sObj.val()=='a'){ sObj.hide(); jQuery('#liInpCst'+ii).show(); } 
      }
      function nxs_li2GetPages(ii,force){ var u = jQuery('#apLIUName'+ii).val(); var p = jQuery('#apLIPass'+ii).val(); jQuery('#nxsLI2InfoDiv'+ii).show(); var pgcID = jQuery('#li2pgID'+ii).val(); var pggID = jQuery('#li2GpgID'+ii).val(); jQuery("#li2pgID"+ii).focus();
            jQuery('#li'+ii+'2rfrshImg').hide();  jQuery('#li'+ii+'2ldImg').show();   jQuery('#li'+ii+'3ldImg').show(); jQuery("#nxsLI2MsgDiv"+ii).html("&nbsp;"); jQuery("#li2pgID"+ii).html("<option value=\"\">Getting Pages.......</option>");
            jQuery.post(ajaxurl,{action: 'nxs_snap_aj',"nxsact":"getItFromNT", "fName":"getListOfPagesNXS", nt:"LI", u:u, p:p, ii:ii, pgcID:pgcID, force:force, isOut:1, nxs_mqTest:"'", _wpnonce: jQuery('#nxsSsPageWPN_wpnonce').val()}, function(j){  
               if (j.indexOf('<option')>-1) jQuery("#li2pgID"+ii).html(j); else jQuery("#nxsLI2MsgDiv"+ii).html(j); jQuery('#li'+ii+'2ldImg').hide(); jQuery('#nxsLI2GInfoDiv'+ii).show(); jQuery("#li2GpgID"+ii).html("<option value=\"\">Getting Groups.......</option>");                 
               jQuery.post(ajaxurl,{action: 'nxs_snap_aj',"nxsact":"getItFromNT", "fName":"getListOfGroupsNXS", nt:"LI", u:u, p:p, ii:ii, pggID:pggID, force:force, isOut:1, nxs_mqTest:"'", _wpnonce: jQuery('#nxsSsPageWPN_wpnonce').val()}, function(j){  
                  if (j.indexOf('<option')>-1) jQuery("#li2GpgID"+ii).html(j); else jQuery("#nxsLI2MsgDiv"+ii).html(j);  jQuery('#li'+ii+'3ldImg').hide(); jQuery('#li'+ii+'2rfrshImg').show();
               }, "html")                  
            }, "html")          
      }
      function nxs_li2PageChange(ii, sObj){  
          if (sObj.val()=='a'){ sObj.hide(); jQuery('#li2InpCst'+ii).show(); jQuery("#li2InpCst"+ii).focus(); } 
      }     
      function nxs_li2GPageChange(ii, sObj){  
          if (sObj.val()=='a'){ sObj.hide(); jQuery('#li2GInpCst'+ii).show(); jQuery("#li2GInpCst"+ii).focus(); } 
      }
//## XI
      function nxs_xi2GetPages(ii,force){ var u = jQuery('#apXIUName'+ii).val(); var p = jQuery('#apXIPass'+ii).val(); jQuery('#nxsXI2InfoDiv'+ii).show(); var pgcID = jQuery('#xi2pgID'+ii).val(); var pggID = jQuery('#xi2GpgID'+ii).val(); jQuery("#xi2pgID"+ii).focus();
            jQuery('#xi'+ii+'2rfrshImg').hide();  jQuery('#xi'+ii+'2ldImg').show();   jQuery('#xi'+ii+'3ldImg').show(); jQuery("#nxsxi2MsgDiv"+ii).html("&nbsp;"); jQuery("#xi2pgID"+ii).html("<option value=\"\">Getting Pages.......</option>");
            jQuery.post(ajaxurl,{action: 'nxs_snap_aj',"nxsact":"getItFromNT", "fName":"getPgsList", nt:"XI", u:u, p:p, ii:ii, pgcID:pgcID, force:force, isOut:1, nxs_mqTest:"'", _wpnonce: jQuery('#nxsSsPageWPN_wpnonce').val()}, function(j){  
               if (j.indexOf('<option')>-1) jQuery("#xi2pgID"+ii).html(j); else jQuery("#nxsXI2MsgDiv"+ii).html(j); jQuery('#xi'+ii+'2ldImg').hide(); jQuery('#nxsXI2GInfoDiv'+ii).show(); jQuery("#xi2GpgID"+ii).html("<option value=\"\">Getting Groups.......</option>");                 
               jQuery.post(ajaxurl,{action: 'nxs_snap_aj',"nxsact":"getItFromNT", "fName":"getGrpList", nt:"XI", u:u, p:p, ii:ii, pggID:pggID, force:force, isOut:1, nxs_mqTest:"'", _wpnonce: jQuery('#nxsSsPageWPN_wpnonce').val()}, function(j){  
                  if (j.indexOf('<option')>-1) jQuery("#xi2GpgID"+ii).html(j); else jQuery("#nxsXI2MsgDiv"+ii).html(j); pggID = jQuery('#xi2GpgID'+ii).val();  var pgfID = jQuery('#xi2GfID'+ii).val(); 
                  
                  jQuery.post(ajaxurl,{action: 'nxs_snap_aj',"nxsact":"getItFromNT", "fName":"getGrpForums", nt:"XI", u:u, p:p, ii:ii, pggID:pggID, pgfID:pgfID, force:force, isOut:1, nxs_mqTest:"'", _wpnonce: jQuery('#nxsSsPageWPN_wpnonce').val()}, function(j){  
                     if (j.indexOf('<option')>-1) jQuery("#xi2GfID"+ii).html(j); else jQuery("#nxsXI2MsgDiv"+ii).html(j);  jQuery('#xi'+ii+'3ldImg').hide(); jQuery('#xi'+ii+'2rfrshImg').show();
                  }, "html")
                  
                  
               }, "html")
            }, "html")          
      }       
      function nxs_xi2PageChange(ii, sObj){  
          if (sObj.val()=='a'){ sObj.hide(); jQuery('#xi2InpCst'+ii).show(); jQuery("#xi2InpCst"+ii).focus(); } 
      }     
      function nxs_xi2GPageChange(ii, sObj){  
          if (sObj.val()=='a'){ sObj.hide(); jQuery('#xi2GInpCst'+ii).show(); jQuery("#xi2GInpCst"+ii).focus(); } 
            else { jQuery('#xi'+ii+'3ldImg').show(); jQuery('#xi'+ii+'2rfrshImg').hide(); 
            
             var pggID = jQuery('#xi2GpgID'+ii).val();  var pgfID = jQuery('#xi2GfID'+ii).val();  var u = jQuery('#apXIUName'+ii).val(); var p = jQuery('#apXIPass'+ii).val();
                  
                  jQuery.post(ajaxurl,{action: 'nxs_snap_aj',"nxsact":"getItFromNT", "fName":"getGrpForums", nt:"XI", u:u, p:p, ii:ii, pggID:pggID, pgfID:pgfID, force:1, isOut:1, nxs_mqTest:"'", _wpnonce: jQuery('#nxsSsPageWPN_wpnonce').val()}, function(j){  
                     if (j.indexOf('<option')>-1) jQuery("#xi2GfID"+ii).html(j); else jQuery("#nxsXI2MsgDiv"+ii).html(j);  jQuery('#xi'+ii+'3ldImg').hide(); jQuery('#xi'+ii+'2rfrshImg').show();
                  }, "html")
                  
            
            }
      }          
      function nxs_xi2GfChange(ii, sObj){  
          if (sObj.val()=='a'){ sObj.hide(); jQuery('#xi2GfInpCst'+ii).show(); jQuery("#xi2GfInpCst"+ii).focus(); } 
      }          
//## FB      
      function nxs_fbGetPages(ii,force){ var u = jQuery('#fbappKey'+ii).val(); var p = jQuery('#fbAuthUser'+ii).val(); var pgID = jQuery('#fbpgID'+ii).val(); jQuery("#fbpgID"+ii).focus();
            jQuery('#fb'+ii+'rfrshImg').hide();  jQuery('#fb'+ii+'ldImg').show(); jQuery("#nxsFBMsgDiv"+ii).html("&nbsp;"); jQuery("#fbpgID"+ii).html("<option value=\"\">Getting Pages.......</option>");
            jQuery.post(ajaxurl,{action: 'nxs_snap_aj',"nxsact":"getItFromNT", "fName":"getListOfPages", nt:"FB", u:u, p:p, ii:ii, pgID:pgID, force:force, isOut:1, nxs_mqTest:"'", _wpnonce: jQuery('#nxsSsPageWPN_wpnonce').val()}, function(j){  
                 if (j.indexOf('<option')>-1) jQuery("#fbpgID"+ii).html(j); else jQuery("#nxsFBMsgDiv"+ii).html(j); jQuery('#fb'+ii+'ldImg').hide(); jQuery('#fb'+ii+'rfrshImg').show();
            }, "html")          
      }
      function nxs_fbPageChange(ii, sObj){  
          if (sObj.val()=='a'){ sObj.hide(); jQuery('#fbInpCst'+ii).show(); jQuery("#fbInpCst"+ii).focus(); } 
      }                 
      
//## Common Input to DropDown Function.             
      function nxs_InpToDDChange(tObj) { var sObj = jQuery('#'+tObj.data('tid')); sObj.prepend( jQuery("<option/>", { value: tObj.val(), text: tObj.val() })); tObj.hide(); sObj.prop("selectedIndex", 0).trigger('change'); sObj.show(); }            
      function nxs_InpToDDBlur(tObj) {  var sObj = jQuery('#'+tObj.data('tid')); tObj.hide(); sObj.prop("selectedIndex", 0).trigger('change'); sObj.show(); }
//## / API Specific Functions

/*================================================================================
 * @name: bPopup - if you can't get it up, use bPopup * http://dinbror.dk/bpopup * @author: (c)Bjoern Klinggaard (twitter@bklinggaard) * @version: 0.11.0.min
 ================================================================================*/
 (function(c){c.fn.bPopup=function(A,E){function L(){a.contentContainer=c(a.contentContainer||b);switch(a.content){case "iframe":var d=c('<iframe class="b-iframe" '+a.iframeAttr+"></iframe>");d.appendTo(a.contentContainer);t=b.outerHeight(!0);u=b.outerWidth(!0);B();d.attr("src",a.loadUrl);l(a.loadCallback);break;case "image":B();c("<img />").load(function(){l(a.loadCallback);F(c(this))}).attr("src",a.loadUrl).hide().appendTo(a.contentContainer);break;default:B(),c('<div class="b-ajax-wrapper"></div>').load(a.loadUrl,a.loadData,function(d,b,e){l(a.loadCallback,b);F(c(this))}).hide().appendTo(a.contentContainer)}}function B(){a.modal&&c('<div class="b-modal '+e+'"></div>').css({backgroundColor:a.modalColor,position:"fixed",top:0,right:0,bottom:0,left:0,opacity:0,zIndex:a.zIndex+v}).appendTo(a.appendTo).fadeTo(a.speed,a.opacity);C();b.data("bPopup",a).data("id",e).css({left:"slideIn"==a.transition||"slideBack"==a.transition?"slideBack"==a.transition?f.scrollLeft()+w:-1*(x+u):m(!(!a.follow[0]&&n||g)),position:a.positionStyle||"absolute",top:"slideDown"==a.transition||"slideUp"==a.transition?"slideUp"==a.transition?f.scrollTop()+y:z+-1*t:p(!(!a.follow[1]&&q||g)),"z-index":a.zIndex+v+1}).each(function(){a.appending&&c(this).appendTo(a.appendTo)});G(!0)}function r(){a.modal&&c(".b-modal."+b.data("id")).fadeTo(a.speed,0,function(){c(this).remove()});a.scrollBar||c("html").css("overflow","auto");c(".b-modal."+e).unbind("click");f.unbind("keydown."+e);k.unbind("."+e).data("bPopup",0<k.data("bPopup")-1?k.data("bPopup")-1:null);b.undelegate(".bClose, ."+a.closeClass,"click."+e,r).data("bPopup",null);clearTimeout(H);G();return!1}function I(d){y=k.height();w=k.width();h=D();if(h.x||h.y)clearTimeout(J),J=setTimeout(function(){C();d=d||a.followSpeed;var e={};h.x&&(e.left=a.follow[0]?m(!0):"auto");h.y&&(e.top=a.follow[1]?p(!0):"auto");b.dequeue().each(function(){g?c(this).css({left:x,top:z}):c(this).animate(e,d,a.followEasing)})},50)}function F(d){var c=d.width(),e=d.height(),f={};a.contentContainer.css({height:e,width:c});e>=b.height()&&(f.height=b.height());c>=b.width()&&(f.width=b.width());t=b.outerHeight(!0);u=b.outerWidth(!0);C();a.contentContainer.css({height:"auto",width:"auto"});f.left=m(!(!a.follow[0]&&n||g));f.top=p(!(!a.follow[1]&&q||g));b.animate(f,250,function(){d.show();h=D()})}function M(){k.data("bPopup",v);b.delegate(".bClose, ."+a.closeClass,"click."+e,r);a.modalClose&&c(".b-modal."+e).css("cursor","pointer").bind("click",r);N||!a.follow[0]&&!a.follow[1]||k.bind("scroll."+e,function(){if(h.x||h.y){var d={};h.x&&(d.left=a.follow[0]?m(!g):"auto");h.y&&(d.top=a.follow[1]?p(!g):"auto");b.dequeue().animate(d,a.followSpeed,a.followEasing)}}).bind("resize."+e,function(){I()});a.escClose&&f.bind("keydown."+e,function(a){27==a.which&&r()})}function G(d){function c(e){b.css({display:"block",opacity:1}).animate(e,a.speed,a.easing,function(){K(d)})}switch(d?a.transition:a.transitionClose||a.transition){case "slideIn":c({left:d?m(!(!a.follow[0]&&n||g)):f.scrollLeft()-(u||b.outerWidth(!0))-200});break;case "slideBack":c({left:d?m(!(!a.follow[0]&&n||g)):f.scrollLeft()+w+200});break;case "slideDown":c({top:d?p(!(!a.follow[1]&&q||g)):f.scrollTop()-(t||b.outerHeight(!0))-200});break;case "slideUp":c({top:d?p(!(!a.follow[1]&&q||g)):f.scrollTop()+y+200});break;default:b.stop().fadeTo(a.speed,d?1:0,function(){K(d)})}}function K(d){d?(M(),l(E),a.autoClose&&(H=setTimeout(r,a.autoClose))):(b.hide(),l(a.onClose),a.loadUrl&&(a.contentContainer.empty(),b.css({height:"auto",width:"auto"})))}function m(a){return a?x+f.scrollLeft():x}function p(a){return a?z+f.scrollTop():z}function l(a,e){c.isFunction(a)&&a.call(b,e)}function C(){z=q?a.position[1]:Math.max(0,(y-b.outerHeight(!0))/2-a.amsl);x=n?a.position[0]:(w-b.outerWidth(!0))/2;h=D()}function D(){return{x:w>b.outerWidth(!0),y:y>b.outerHeight(!0)}}c.isFunction(A)&&(E=A,A=null);var a=c.extend({},c.fn.bPopup.defaults,A);a.scrollBar||c("html").css("overflow","hidden");var b=this,f=c(document),k=c(window),y=k.height(),w=k.width(),N=/OS 6(_\d)+/i.test(navigator.userAgent),v=0,e,h,q,n,g,z,x,t,u,J,H;b.close=function(){r()};b.reposition=function(a){I(a)};return b.each(function(){c(this).data("bPopup")||(l(a.onOpen),v=(k.data("bPopup")||0)+1,e="__b-popup"+v+"__",q="auto"!==a.position[1],n="auto"!==a.position[0],g="fixed"===a.positionStyle,t=b.outerHeight(!0),u=b.outerWidth(!0),a.loadUrl?L():B())})};c.fn.bPopup.defaults={amsl:50,appending:!0,appendTo:"body",autoClose:!1,closeClass:"b-close",content:"ajax",contentContainer:!1,easing:"swing",escClose:!0,follow:[!0,!0],followEasing:"swing",followSpeed:500,iframeAttr:'scrolling="no" frameborder="0"',loadCallback:!1,loadData:!1,loadUrl:!1,modal:!0,modalClose:!0,modalColor:"#000",onClose:!1,onOpen:!1,opacity:.7,position:["auto","auto"],positionStyle:"absolute",scrollBar:!0,speed:250,transition:"fadeIn",transitionClose:!1,zIndex:9997}})(jQuery);