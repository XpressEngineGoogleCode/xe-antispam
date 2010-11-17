function completeInsertMemoItem(ret_obj) {
    alert(ret_obj['message']);

    var url = request_uri.setQuery('mid', current_mid).setQuery('memo_srl', ret_obj['memo_srl']).setQuery('act','dispMemoList');
    if(typeof(xeVid)!='undefined') url = url.setQuery('vid', xeVid);
    location.href = url;
}


function deleteMemoItem(memo_srl){
    var params = new Array();
    params['memo_srl'] = memo_srl;
    params['mid'] = current_mid;
	
	var response_tags = new Array('error','message');
    exec_xml('memo', 'procMemoAdminItemDelete', params, completeReload, response_tags);
}

function completeReload(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    location.reload();
}

function insertSelectedModules(id, module_srl, mid, browser_title) {
    var sel_obj = xGetElementById('_'+id);
    for(var i=0;i<sel_obj.options.length;i++) if(sel_obj.options[i].value==module_srl) return;
    var opt = new Option(browser_title+' ('+mid+')', module_srl, false, false);
    sel_obj.options[sel_obj.options.length] = opt;
    if(sel_obj.options.length>8) sel_obj.size = sel_obj.options.length;

    syncMid(id);
}

function syncMid(id) {
    var sel_obj = xGetElementById('_'+id);
    var valueArray = [];
    for(var i=0;i<sel_obj.options.length;i++) valueArray.push(sel_obj.options[i].value);
	jQuery('#'+id).val(valueArray.join(','));
}

function midRemove(id) {
    var sel_obj = xGetElementById('_'+id);
    if(sel_obj.selectedIndex<0) return;
    var idx = sel_obj.selectedIndex;
    sel_obj.remove(idx);
    idx = idx-1;
    if(idx < 0) idx = 0;
    if(sel_obj.options.length) sel_obj.selectedIndex = idx;

    syncMid(id);
}


// filter
var validator = xe.getApp('Validator')[0];
validator.cast('ADD_RULE',['check_score',function(v){
	return (v>=0 && v<=100);
}]);
validator.cast('ADD_MESSAGE',['invalid_check_score','check score']);

function insertSelected(target, val, text) {
    var sel_obj = jQuery('#_'+target).get(0);
    for(var i=0;i<sel_obj.options.length;i++) if(sel_obj.options[i].value==val) return;
    var opt = new Option(text, val, false, false);
    sel_obj.options[sel_obj.options.length] = opt;
    if(sel_obj.options.length>8) sel_obj.size = sel_obj.options.length;

    syncMid(target);
}

function filterInsertConfig(obj){
	jQuery('select.selected',obj).each(function(){
		var id = jQuery(this).attr('id').substring(1);
		jQuery('#'+id+'_info').val(getSelectBoxInfo(this));
	});
	return procFilter(obj,insert_config);
}
function getSelectBoxInfo(obj){
	var info = [];
    for(var i=0;i<obj.options.length;i++){
		info.push(obj.options[i].text);
	}
	return info.join('|@|');
}

function restoreKeepObject(srl){
    var params = new Array();
	var response_tags = new Array('error','message');

	if(srl.match(/[0-9],/)) srl = srl.replace(/,$/,'');
    params['nspam_keep_srl'] = srl;

    exec_xml('nspam', 'procNspamAdminRestoreObject', params, completeReload, response_tags);
}

function deleteKeepObject(srl){
    var params = new Array();
	var response_tags = new Array('error','message');

	if(srl.match(/[0-9],/)) srl = srl.replace(/,$/,'');
    params['nspam_keep_srl'] = srl;

    exec_xml('nspam', 'procNspamAdminDeleteObject', params, completeReload, response_tags);
}

function restoreKeepObjects(){
	var srls = []
	jQuery('input.check_content_srl:checked').each(function(){
		srls.push(jQuery(this).val());
	});
	
	if(srls.length>0){
		restoreKeepObject(srls.join(','));
	}
}

function deleteKeepObjects(){
	var srls = []
	jQuery('input.check_content_srl:checked').each(function(){
		srls.push(jQuery(this).val());
	});
	
	if(srls.length>0){
		deleteKeepObject(srls.join(','));
	}
}

function sendSpamContents(type, msg_no_article_selected){
	var srls = []
	jQuery('input.check_content_srl:checked').each(function(){
		srls.push(jQuery(this).val());
	});
	
	if(srls.length<1) return alert(msg_no_article_selected);

	srls = srls.join(',');
    var params = new Array();
	var response_tags = new Array('error','message');

	if(srls.match(/[0-9],/)) srls = srls.replace(/,$/,'');
    params['srls'] = srls;
    params['type'] = type;

    exec_xml('nspam', 'procNspamAdminPutSpamContents', params, completeReload, response_tags);
}

function doSpamProccess(type, msg_no_article_selected){
	var srls = []
	jQuery('input.check_content_srl:checked').each(function(){
		srls.push(jQuery(this).val());
	});
	
	if(srls.length<1) return alert(msg_no_article_selected);

	srls = srls.join(',');
    var params = new Array();
	var response_tags = new Array('error','message');

	if(srls.match(/[0-9],/)) srls = srls.replace(/,$/,'');
    params['srls'] = srls;
    params['type'] = type;

    exec_xml('nspam', 'procNspamAdminDoProccess', params, completeReload, response_tags);
}


function doCancelDocumentDeclare() {
	var document_srl = new Array();
	jQuery('#fo_list input[name=cart]:checked').each(function() {
			document_srl[document_srl.length] = jQuery(this).val();
			}); 

	if(document_srl.length<1) return;

	var params = new Array();
	params['document_srl'] = document_srl.join(',');

	exec_xml('document','procDocumentAdminCancelDeclare', params, completeCancelDeclare);
}
function doCancelDeclareComment() {
	var comment_srl = new Array();
	jQuery('#fo_list input[name=cart]:checked').each(function() {
			comment_srl[comment_srl.length] = jQuery(this).val();
			}); 

	if(comment_srl.length<1) return;

	var params = new Array();
	params['comment_srl'] = comment_srl.join(',');

	exec_xml('comment','procCommentAdminCancelDeclare', params, function() { location.reload(); }); 
}

function completeCancelDeclare(ret_obj) {
	location.reload();
}

function doDeleteDeniedIP(ipaddress) {
    var fo_obj = xGetElementById('fo_denied_ip');
    fo_obj.ipaddress.value = ipaddress;
    procFilter(fo_obj, delete_denied_ip);
}

function doDeleteDeniedIPs() {
	var ipaddrs = [];

	jQuery('input.check_denied_ip:checked').each(
		function () {
			ipaddrs.push(jQuery(this).val());
		});
	
	if (ipaddrs.length < 1) return;

	ipaddrs = ipaddrs.join(',');

	var params = new Array();
	var response_tags = new Array('error', 'message');

	params['ipaddrs'] = ipaddrs;

	exec_xml('nspam', 'procNspamAdminDeleteDeniedIPs', params, completeReload, response_tags);
}


function testGetSpamScore(){
	var dics = [];
	jQuery('#_use_spamdics>option').each(function(){
		dics.push(jQuery(this).val());
	});
	var filters = [];
	jQuery('#_use_spamfilters>option').each(function(){
		filters.push(jQuery(this).val());
	});

    var params = new Array();
	var response_tags = new Array('error','message','score');

    params['test_content'] = jQuery('#test_content').val();
    params['filters'] = filters.join(',');
    params['dics'] = dics.join(',');

    exec_xml('nspam', 'procNspamAdminTestGetSpamScore', params, function(ret_obj){ alert(ret_obj['score']);}, response_tags);

}

function checkSpamConfig(message){
	alert(message);
}

function doSpamProccessAllDocument(page) {
	jQuery('.mwProgress').show();

	var response_tags = new Array('error','message','cur_page','total_page','total_count');
	var params = new Array();
	params['page'] = page;

	exec_xml('nspam','procNspamAdminAllDocumentDoProccess', params, function(ret_obj){
		progress(ret_obj,doSpamProccessAllDocument);
	}, response_tags); 
}

function doSendSpamAllDocumentTrash(page) {
	jQuery('.mwProgress').show();

	var response_tags = new Array('error','message','cur_page','total_page','total_count');
	var params = new Array();
	params['page'] = page;

	exec_xml('nspam','procNspamAdminAllDocumentTrashSendSpam', params, function(ret_obj){
		progress(ret_obj,doSendSpamAllDocumentTrash);
	}, response_tags); 
}

function doSpamProccessAllComment(page) {
	jQuery('.mwProgress').show();

	var response_tags = new Array('error','message','cur_page','total_page','total_count');
	var params = new Array();
	params['page'] = page;

	exec_xml('nspam','procNspamAdminAllCommentDoProccess', params, function(ret_obj){
		progress(ret_obj,doSpamProccessAllComment);
	}, response_tags); 
}

function doSpamProccessAllTrackback(page) {
	jQuery('.mwProgress').show();

	var response_tags = new Array('error','message','cur_page','total_page','total_count');
	var params = new Array();
	params['page'] = page;

	exec_xml('nspam','procNspamAdminAllTrackbackDoProccess', params, function(ret_obj){
		progress(ret_obj,doSpamProccessAllTrackback);
	}, response_tags); 
}

function progress(ret_obj, func){
	var cur_page = parseInt(ret_obj['cur_page']);
	var total_page = parseInt(ret_obj['total_page']);
	var total_count = parseInt(ret_obj['total_count']);
	var percent = parseInt((cur_page/total_page)*100);

	jQuery('.mwProgress .pAction').css('width',percent+'%');
	jQuery('.mwProgress .pPercent strong').html(percent);

	if(cur_page >= total_page){
		jQuery('.mwProgress').hide();
		jQuery('.mwProgress .pAction').css('width','0%');
		jQuery('.mwProgress .pPercent strong').val(0);
	}else{
		func(cur_page+1);
	}
}



