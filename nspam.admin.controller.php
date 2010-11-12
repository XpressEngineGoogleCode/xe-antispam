<?php
	/**
	 * @class  nspamAdminController
	 * @author nhn (developers@xpressengine.com)
	 * @brief  nspam admin controller class
	 **/

	class nspamAdminController extends nspam {

		function init() {
		}

		/**
		 * @brief 설정저장
		 **/
		function procNspamAdminConfigUpdate() {
			$oModuleController = &getController('module');
			$oModuleModel = &getModel('module');

			$vars = Context::getRequestVars();
			if(!$vars->type || !in_array($vars->type,array('document','comment','trackback'))) return new Object(-1,'msg_invalid_request');

			$obj = new stdClass;
			$obj->use_nspam = $vars->use_nspam=='Y'?'Y':'N';

			$use_spamfilters = explode(',',$vars->use_spamfilters);
			$use_spamfilters_info = explode('|@|',$vars->use_spamfilters_info);
			$use_spamdics = explode(',',$vars->use_spamdics);
			$use_spamdics_info = explode('|@|',$vars->use_spamdics_info);

		
			$obj->use_spamfilters = array();
			for($i=0,$c=count($use_spamfilters);$i<$c;$i++){
				if($use_spamfilters[$i] && $use_spamfilters_info[$i]){
					$obj->use_spamfilters[$use_spamfilters[$i]] = $use_spamfilters_info[$i];
				}
			}
			$obj->use_spamdics = array();
			for($i=0,$c=count($use_spamdics);$i<$c;$i++){
				if($use_spamdics[$i] && $use_spamdics_info[$i]){
					$obj->use_spamdics[$use_spamdics[$i]] = $use_spamdics_info[$i];
				}
			}

			$obj->score_trash_content = $vars->score_trash_content;
			$obj->score_denied_ip = $vars->score_denied_ip;
			$obj->score_deny_user = $vars->score_deny_user;
			$obj->module_apply = $vars->module_apply=='allow'?'allow':'deny';

			$target_module = explode(',',$vars->target_module);
			$obj->target_module = array();
			foreach($target_module as $k => $v){
				if($v) $obj->target_module[] = $v;
			}

			$config = $oModuleModel->getModuleConfig('nspam');
			$config->{$vars->type} = $obj;

			if($vars->use_nspam_document == 'Y') $config->document = $obj;
			if($vars->use_nspam_comment == 'Y') $config->comment =  $obj;
			if($vars->use_nspam_trackback == 'Y') $config->trackback = $obj;

			$output = $oModuleController->insertModuleConfig('nspam',$config);
		}

		/**
		 * @brief 보관중인 Object를 삭제
		 **/
		function procNspamAdminDeleteObject(){
			$nspam_keep_srl = Context::get('nspam_keep_srl');
			if(!$nspam_keep_srl) return new Object(-1,'msg_invalid_request');
			
			$obj->nspam_keep_srl = $nspam_keep_srl;
			$output = executeQuery('nspam.deleteKeep',$obj);

			return $output;
		}

		/**
		 * @brief 보관중인 Object를 복원
		 **/
		function procNspamAdminRestoreObject(){
			$nspam_keep_srl = Context::get('nspam_keep_srl');
			if(!$nspam_keep_srl) return new Object(-1,'msg_invalid_request');

			$srls = explode(',',$nspam_keep_srl);
			foreach($srls as $i => $srl){
				if($srl){
					$output = $this->restoreObject($srl);
					if(!$output->toBool()) return $output;
				}
			}
		}

		/**
		 * @brief 보관중인 Object를 복원
		 **/
		function restoreObject($nspam_keep_srl){
			$vars->nspam_keep_srl = $nspam_keep_srl;
			$output = executeQuery('nspam.getKeep',$vars);
			if(!$output->toBool() || !$output->data) return new Object(-1,'msg_invalid_request');

			$keep = $output->data;
			$obj = unserialize($keep->data);
			$obj->_form_nspam_keep_ = true;
			$obj->regdate = $keep->regdate;

			if($keep->type =='document'){
				$output = $this->restoreDocument($obj);
			}else if($keep->type =='comment'){
				$output = $this->restoreComment($obj);
			}else if($keep->type =='trackback'){
				$output = $this->restoreTrackback($obj);
			}

			return $output;
		}

		/**
		 * @brief 보관중인 Document 복원
		 **/
		function restoreDocument($obj){
			$var->list_order = $var->update_order = $obj->document_srl * -1;
			$var->document_srl = $obj->document_srl;
			$var->module_srl = $obj->module_srl;
			$var->regdate = $obj->regdate;
			
			$oDocumentController = &getController('document');
			$output = $oDocumentController->insertDocument($obj,true);
			if(!$output->toBool()) return $output;
			
			$output = executeQuery('document.updateDocumentOrder',$var);

			$robj->nspam_keep_srl = $var->document_srl;
			$output = executeQuery('nspam.deleteKeep',$robj);

			return $output;
		}

		/**
		 * @brief 보관중인 Comment 복원
		 **/
		function restoreComment($obj){
			$oCommentModel = &getModel('comment');
			$oComment = $oCommentModel->getComment($obj->comment_srl);

			// 자식 댓글이 있어 삭제하지 못한 경우
			if($oComment->isExists()){
				$output = executeQuery('comment.updateComment',$obj);
			}else{

				$var->list_order = $obj->comment_srl * -1;
				$var->comment_srl = $obj->comment_srl;
				$var->regdate = $obj->regdate;

				$oCommentController = &getController('comment');
				$output = $oCommentController->insertComment($obj,true);
				if(!$output || !$output->toBool()) return new Object(-1,'msg_restore_error');

				$output = executeQuery('nspam.updateCommentOrder',$var);
			}

			$robj->nspam_keep_srl = $var->comment_srl;
			$output = executeQuery('nspam.deleteKeep',$robj);

			return $output;
		}

		/**
		 * @brief 보관중인 Trackback 복원
		 **/
		function restoreTrackback($obj){
			$var->list_order = $obj->trackback_srl * -1;
			$var->regdate = $obj->regdate;

			$oTrackbackController = &getController('trackback');
			$output = $oTrackbackController->insertTrackback($obj);
			if(!$output || !$output->toBool()) return new Object(-1,'msg_restore_error');

			$robj->nspam_keep_srl = $var->trackback_srl;
			$output = executeQuery('nspam.deleteKeep',$robj);
			return $output;
		}
		

		/**
		 * @brief 스팸 콘텐트를 스팸서버에 전송
		 **/
		function procNspamAdminPutSpamContents(){
			$type = Context::get('type');
			if(!$type || !in_array($type,array('document','comment','trackback'))) return new Object(-1,'msg_invalid_request');

			$srls = Context::get('srls');
			$srls = explode(',',$srls);
			if(count($srls)<1) return new Object(-1,'msg_invalid_request');

			$output = $this->requestPutSpamContents($srls,$type);
		}

		/**
		 * @brief 스팸 콘텐트를 스팸서버에 전송
		 **/
		function requestPutSpamContents($srls,$type='document'){

			if($type=='document'){
				$output = $this->_requestPutSpamDocuments($srls);
			}else if($type=='comment'){
				$output = $this->_requestPutSpamComments($srls);
			}else if($type=='trackback'){
				$output = $this->_requestPutSpamTrackbacks($srls);
			}

			if(!$output->toBool()) return $output;
		}

		/**
		 * @brief 스팸 Document 콘텐트를 스팸서버에 전송 및 삭제
		 **/
		function _requestPutSpamDocuments($srls){
			$oDocumentModel = &getModel('document');
			$oDocumentList = $oDocumentModel->getDocuments($srls);

			$oReq = new RequestPutSpamContents();

			$c = 0;
			foreach($oDocumentList as $i => $oDocument){
				$oReq->addContent($oDocument->document_srl, $oDocument->get('content'),$oDocument->get('title'),$oDocument->get('ipaddress'),zdate($oDocument->get('regdate'), 'Y-m-d H:i:s'));
				$c++;
			}

			if($c>0){
				$output = $oReq->request();
				if($output->error == 0 || $output->contents || is_array($output->contents->item)){
					$oDocumentController = &getController('document');

					$item = $output->contents->item;
					if($item){
						foreach($item as $i){
							$output = $oDocumentController->deleteDocument($i->id,true);
							if(!$output->toBool()) return $output;
						}
					}
				}
			}

			return new Object();
		}

		/**
		 * @brief 스팸 Comment 콘텐트를 스팸서버에 전송 및 삭제
		 **/
		function _requestPutSpamComments($srls){
			$oCommentModel = &getModel('comment');
			$oCommentList = $oCommentModel->getComments($srls);
			$oReq = new RequestPutSpamContents();

			$c = 0;
			foreach($oCommentList as $i => $oComment){
				$oReq->addContent($oComment->comment_srl, $oComment->get('content'),'',$oComment->get('ipaddress'),zdate($oComment->get('regdate'), 'Y-m-d H:i:s'));
				$c++;
			}

			if($c>0){
				$output = $oReq->request();

				if($output->error == 0 || $output->contents || is_array($output->contents->item)){
					$oCommentController = &getController('comment');

					$item = $output->contents->item;
					if($item){
						foreach($item as $i){
							$output = $oCommentController->deleteComment($i->id,true);
							if(!$output->toBool()) return $output;
						}
					}
				}

			}

			return new Object();
		}

		/**
		 * @brief 스팸 trackback 콘텐트를 스팸서버에 전송 및 삭제
		 **/
		function _requestPutSpamTrackbacks($srls){
			$var = new stdClass;
			$var->trackback_srl = join(',',$srls);
			$output = executeQueryArray('nspam.getTrackback',$var);
			if(!$output->toBool()) return $output;

			$oReq = new RequestPutSpamContents();

			$c = 0;
			if($output->data){
				foreach($output->data as $i => $oTrackback){
					$oReq->addContent($oTrackback->trackback_srl, $oTrackback->excerpt,$oTrackback->title,$oTrackback->ipaddress,zdate($oTrackback->regdate, 'Y-m-d H:i:s'));
					$c++;
				}
			}

			if($c>0){
				$output = $oReq->request();
				if($output->error == 0 || $output->contents || is_array($output->contents->item)){
					$oTrackbackController = &getController('trackback');

					$item = $output->contents->item;
					if($item){
						foreach($item as $i){
							$output = $oTrackbackController->deleteTrackback($i->id,true);
							if(!$output->toBool()) return $output;
						}
					}
				}
			}

			return new Object();
		}


		/**
		 * @brief 금지 IP등록
		 **/
		function procNspamAdminInsertDeniedIP() {
			$ipaddress = Context::get('ipaddress');

			$oNspamController = &getController('nspam');
			return $oNspamController->deniedIP($ipaddress);
		}

		/**
		 * @brief 금지 IP삭제
		 **/
		function procNspamAdminDeleteDeniedIP() {
			$ipaddress = Context::get('ipaddress');

			return $this->deleteIP($ipaddress);
		}

		/**
		 * @brief 
		 **/
		function procNspamAdminDeleteDeniedIPs() {
			$ipaddrs = Context::get('ipaddrs');

			$ipaddrs = explode(",", $ipaddrs);

			return $this->deleteIPs($ipaddrs);
		}

		/**
		 * @brief IP 제거
		 * 스패머로 등록된 IP를 제거
		 **/
		function deleteIP($ipaddress) {
			if(!$ipaddress) return;

			$args->ipaddress = $ipaddress;
			$output = executeQuery('nspam.deleteDeniedIP', $args);

			return $output;
		}

		function deleteIPs($ipaddrs) {
			$output = null;

			foreach ($ipaddrs as $ip) {
				$output = $this->deleteIP($ip);

				if ($output->toBool())
					continue;
				return $output;
			}
			return $output;
		}

		function procNspamAdminAllDocumentDoProccess(){

			$type = 'document';
			$page = Context::get('page');

			$obj = new stdClass;
			$obj->page = $page;

			$oDocumentModel = &getModel('document');
			$oNspamController = &getController('nspam');
			$oNspamModel = &getModel('nspam');

			// load config
			$config = $oNspamModel->getNspamPartConfig($type);
			if(!$config || $config->use_nspam!='Y') return new Object(-1,'msg_dont_use_nspam');

			// target module
			if(is_array($config->target_module) && count($config->target_module)>0){
				if($config->module_apply=='deny'){
					$obj->exclude_module_srl = join(',',$config->target_module);
				}else if($part_config->module_apply=='allow'){
					$obj->module_srl = join(',',$config->target_module);
				}
			}

			// request init
			$oReq = new RequestGetSpamScores();

			// 사용 중인 스팸 필터 정보를 읽어와 설정
			$filters = $oNspamModel->getUseSpamFilters($type);
			if (!$filters || count(filters) < 1) return new Object(-1, 'msg_no_spamfilter_specified');
				
			if($filters) $oReq->addSpamFilters($filters);
			$dics = $oNspamModel->getUseSpamDics($type);
			

			if($dics) $oReq->addSpamDics($dics);

			// load document
			$output = $oDocumentModel->getDocumentList($obj,false,false);
			$oDocumentList = $output->data;

			$this->add('cur_page',$output->page_navigation->cur_page);
			$this->add('total_page',$output->page_navigation->total_page);
			$this->add('total_count',$output->page_navigation->total_count);

			$document_srls = array();

			foreach($oDocumentList as $k => $oDocument){
				$oReq->addContent($oDocument->document_srl,
				str_replace('&nbsp;', ' ', $oDocument->get('content')), 
				$oDocument->get('title'),
				$oDocument->get('ipaddress'),
				zdate($oDocument->get('regdate'), 'Y-m-d H:i:s'));
			}

			$output = $oReq->request();
			if(!$output || $output->error != 0 || !$output->scores || !$output->scores->item) return new Object(-1,'msg_spamapi_error');

			$items = array_reverse($output->scores->item);

			foreach($items as $i => $item){
				if($item->score < 1) continue;

				unset($obj);
				$obj = new stdClass;
				$obj->document_srl = $item->id;
				
				// 글을 쓴 회원의 member_srl
				$author_srl = $oDocumentList[$i+1]->variables['member_srl'];

				$output = $oNspamController->doSpamBatchProcess($obj, $item, $type, $author_srl);
			}
		}

		function procNspamAdminAllCommentDoProccess(){
			$type = 'comment';
			$page = Context::get('page');

			$obj = new stdClass;
			$obj->page = $page;

			$oCommenttModel = &getModel('comment');
			$oNspamController = &getController('nspam');
			$oNspamModel = &getModel('nspam');

			// load config
			$config = $oNspamModel->getNspamPartConfig($type);
			if(!$config || $config->use_nspam!='Y') return new Object(-1,'msg_dont_use_nspam');

			// target module
			if(is_array($config->target_module) && count($config->target_module)>0){
				if($config->module_apply=='deny'){
					$obj->exclude_module_srl = join(',',$config->target_module);
				}else if($part_config->module_apply=='allow'){
					$obj->module_srl = join(',',$config->target_module);
				}
			}

			// request init
			$oReq = new RequestGetSpamScores();

			$filters = $oNspamModel->getUseSpamFilters($type);
			if (!$filters || count($filters) < 1) return new Object(-1, 'msg_no_spamfilter_specified');

			if($filters) $oReq->addSpamFilters($filters);
			$dics = $oNspamModel->getUseSpamDics($type);
			if($dics) $oReq->addSpamDics($dics);

			// load document
			$output = $oCommentModel->getCommentList($obj,false,false);
			$oCommentList = $output->data;

			$this->add('cur_page',$output->page_navigation->cur_page);
			$this->add('total_page',$output->page_navigation->total_page);
			$this->add('total_count',$output->page_navigation->total_count);

			$comment_srls = array();
			$keep_srls = array();
			foreach($oCommentList as $k => $oComment){
				array_push($comment_srls,$oComment->comment_srl);
			}

			if(count($comment_srls)>0){
				$obj = new stdClass;
				$obj->nspam_keep_srl = join(',',$comment_srls);
				$keep_output = executeQueryArray('nspam.getKeeps',$obj);

				if($keep_output->toBool() && $keep_output->data){
					foreach($keep_output->data as $k => $oKeep){
						array_push($keep_srls,$oKeep->nspam_keep_srl);
					}
				}
			}

			foreach($oCommentList as $k => $oComment){
				if(!in_array($oComment->comment_srl,$keep_srls)){
					$oReq->addContent($oComment->comment_srl,$oComment->get('content'),'',$oComment->get('ipaddress'), zdate($oComment->get('regdate'), 'Y-m-d H:i:s'));
				}
			}

			$output = $oReq->request();
			if(!$output || $output->error != 0 || !$output->scores || !$output->scores->item) return new Object(-1,'msg_spamapi_error');

			// $items 와 $oCommentList 의 순서를 맞추기 위해 리버스
			$items = array_reverse($output->scores->item);
			foreach($items as $i => $item){
				if($item->score < 1) continue;

				unset($obj);
				$obj = new stdClass;
				$obj->comment_srl = $item->id;

				// 글을 쓴 회원의 member_srl
				$author_srl = $oCommentList[$i+1]->variables['member_srl'];
				
				$output = $oNspamController->doSpamBatchProcess($obj, $item, $type, $author_srl);
			}
		}

		/**
		 * @brief 스팸 설정 적용
		 * 기존에 데이타를 스팸 설정 적용을 통해 다시 스팸분류
		 **/
		function procNspamAdminDoProccess(){
			$type = Context::get('type');
			if(!in_array($type,array('document','comment','trackback'))) return new Object(-1,'msg_invalid_request');

			$srls = Context::get('srls');
			$srls = explode(',',$srls);
			if(count($srls)<1) return new Object(-1,'msg_invalid_request');

			$oNspamModel = &getModel('nspam');

			$config = $oNspamModel->getNspamPartConfig($type);
			if(!$config || $config->use_nspam!='Y') return new Object(-1,'msg_dont_use_nspam');

			$filters = $oNspamModel->getUseSpamFilters($type);
			if (!$filters || count($filters) < 1) return new Object(-1, 'msg_no_spamfilter_specified');

			$dics = $oNspamModel->getUseSpamDics($type);

			$oReq = new RequestGetSpamScores();
			if($filters) $oReq->addSpamFilters($filters);
			if($dics) $oReq->addSpamDics($dics);

			$member_srls = array();

			if($type=='document'){

				$oDocumentModel = &getModel('document');
				//$document_list = $oDocumentModel->getDocuments($srls,true,false);
				$obj_list = $oDocumentModel->getDocuments($srls,true,false);

				foreach($obj_list as $k => $oDocument){
					$oReq->addContent($oDocument->document_srl,$oDocument->get('content'),$oDocument->get('title'),$oDocument->get('ipaddress'),zdate($oDocument->get('regdate'), 'Y-m-d H:i:s'));
					$member_srls[$oDocument->document_srl] = $oDocument->get('member_srl');
				}

			}else if($type=='comment'){

				$oCommentModel = &getModel('comment');	
				//$oCommentList = $oCommentModel->getComments($srls);
				$obj_list = $oCommentModel->getComments($srls);

				foreach($obj_list as $i => $oComment){
					$oReq->addContent($oComment->comment_srl, $oComment->get('content'),'',$oComment->get('ipaddress'), zdate($oComment->get('regdate'), 'Y-m-d H:i:s'));
					$member_srls[$oComment->comment_srl] = $oComment->get('member_srl');
				}

			}else if($type=='trackback'){

				$var = new stdClass;
				$var->trackback_srl = join(',',$srls);
				$output = executeQueryArray('nspam.getTrackback',$var);
				if($output->data){
					foreach($output->data as $i => $oTrackback){
						$oReq->addContent($oTrackback->trackback_srl, $oTrackback->excerpt,$oTrackback->title,$oTrackback->ipaddress,zdate($oTrackback->regdate, 'Y-m-d H:i:s'));
					}
				}

			}
		

			$output = $oReq->request();
			if(!$output || $output->error != 0 || !$output->scores || !$output->scores->item) return new Object(-1,'msg_spamapi_error');

			$oNspamController = &getController('nspam');

			$items = $output->scores->item;

			foreach($items as $i => $item){
				if($item->score < 1) continue;

				unset($obj);
				$obj = new stdClass;

				if($type=='document'){
					$obj->document_srl = $item->id;
				}else if($type=='comment'){
					$obj->comment_srl = $item->id;
				}else if($type=='trackback'){
					$obj->trackback_srl = $item->id;
				}
				
				$output = $oNspamController->doSpamBatchProcess($obj, $item, $type, $member_srls[$item->id]);
			}
		}


		function procNspamAdminTestGetSpamScore(){
			$filters = explode(',',Context::get('filters'));
			$dics = explode(',',Context::get('dics'));

			$content = trim(Context::get('test_content'));
			if(!$content) return new Object(-1,'msg_invalid_request');

			$ip = $_SERVER['REMOTE_ADDR'];
			$time = date('Y-m-d H:i:s');

			$oReq = new RequestGetSpamScores();

			if($filters) $oReq->addSpamFilters($filters);
			if($dics) $oReq->addSpamDics($dics);

			$oReq->addContent(1,$content,'',$ip,$time);

			$output = $oReq->request();
			if(!$output || $output->error != 0 || !$output->scores || !$output->scores->item) return new Object(-1,'msg_spamapi_error');

			$item = $output->scores->item[0];

			$this->add('score',$item->score);
		}
	}
?>
