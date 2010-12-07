<?php
	/**
	 * @class  nspamAdminView
	 * @author nhn (developers@xpressengine.com)
	 * @brief  nspam admin view class
	 **/

	class nspamAdminView extends nspam {

		function init() {
			$oModuleModel = &getModel('module');
			$this->config = $oModuleModel->getModuleConfig('nspam');

			$this->setTemplatePath(sprintf("%stpl/",$this->module_path));
			$this->setTemplateFile(strtolower(str_replace('dispNspamAdmin','',$this->act)));
		}

		function dispNspamAdminPopupSpamFilters(){
			$page = Context::get('page');
			$per = Context::get('per');
			$spamfilter_id = Context::get('spamfilter_id');
			$search_target = Context::get('search_target');
			$search_text = Context::get('search_text');

			$oReq = new RequestGetSpamFilters;
			if($spamfilter_id){
				$oReq->setId($spamfilter_id);
			}else{
				if($search_target =='name'){
					$oReq->searchName($search_text);
				}else if($search_target =='description'){
					$oReq->searchDescription($search_text);
				}
				$oReq->setPage($page);
				$oReq->setPer($per);
			}
			$output = $oReq->request();

			if(!$output || !$output->spamfilters || !$output->spamfilters->item) return new Object(-1,'msg_invalid_request');
			$item = $output->spamfilters->item;
			$item = is_object($item) ? array($item) : $item;
		
			Context::set('spamfilter_list', $item);

			$page_navigation = new PageHandler($output->total, ceil($output->total/$output->per), $output->page, $output->per);
			Context::set('page_navigation',$page_navigation);
			Context::set('page', $output->page);
			Context::set('per', $output->per);

			$this->setLayoutFile('popup_layout');
		}

		function dispNspamAdminPopupSpamDics(){
			$page = Context::get('page');
			$per = Context::get('per');

			$oReq = new RequestGetSpamDics;
			if($spamdic_id){
				$oReq->setId($spamdic_id);
			}else{
				if($search_target =='name'){
					$oReq->searchName($search_text);
				}else if($search_target =='description'){
					$oReq->searchDescription($search_text);
				}
				$oReq->setPage($page);
				$oReq->setPer($per);
			}

			$output = $oReq->request();

			if(!$output || !$output->spamdics || !$output->spamdics->item) return new Object(-1,'msg_invalid_request');
			$item = $output->spamdics->item;
			$item = is_object($item) ? array($item) : $item;
			Context::set('spamdic_list', $item);

			$page_navigation = new PageHandler($output->total, ceil($output->total/$output->per), $output->page, $output->per);
			Context::set('page_navigation',$page_navigation);
			Context::set('page', $output->page);
			Context::set('per', $output->per);

			$this->setLayoutFile('popup_layout');
		}

		function dispNspamAdminPopupSpamDicContents() {

			//$spamdic_srl = 34;

			$spamdic_srl = Context::get('id');
			if (!$spamdic_srl) return;

			// 요청 객체를 만들고 인자를 지정.
			$oReq = new RequestGetSpamDicContents;
			$oReq->setId($spamdic_srl);

			// 요청.
			$output = $oReq->request();
			if (!$output || !$output->words || !$output->words->item) return new Object(-1, 'msg_invalid_request');

			$words = array();

			foreach ($output->words->item as $key => $val) {
				$words[] = $val->word;

			}

			Context::set('spamdic_srl', $output->spamdic_srl);
			Context::set('words', $words);

			$this->setLayoutFile('popup_layout');
		}

		function dispNspamAdminConfig(){
			$type = Context::get('type');
			if(!in_array($type,array('document','comment','trackback'))) $type = 'document';
			Context::set('type',$type);

			if($this->config && $this->config->{$type} 
				&& is_array($this->config->{$type}->target_module) 
				&& count($this->config->{$type}->target_module) >0 ){
				$oModuleModel = &getModel('module');
				$modules_info = $oModuleModel->getModulesInfo($this->config->{$type}->target_module);
				Context::set('config_target_modules_info',$modules_info);
			}
			$nspam_config = $this->config->{$type};

			//$nspam_config->score_delete_content = $nspam_config->score_delete_content ? $nspam_config->score_delete_content : 100;
			$nspam_config->score_trash_content = $nspam_config->score_trash_content ? $nspam_config->score_trash_content : 100;
			$nspam_config->score_denied_ip = $nspam_config->score_denied_ip ? $nspam_config->score_denied_ip :100;
			$nspam_config->score_deny_user = $nspam_config->score_deny_user ? $nspam_config->score_deny_user : 100;

			Context::set('nspam_config',$nspam_config);
		}

		function dispNspamAdminDocumentList(){

			// 목록을 구하기 위한 옵션
			$args->page = Context::get('page'); ///< 페이지
			$args->list_count = 30; ///< 한페이지에 보여줄 글 수
			$args->page_count = 10; ///< 페이지 네비게이션에 나타날 페이지의 수
			$args->sort_index = 'list_order'; ///< 소팅 값

			$args->search_target = Context::get('search_target'); ///< 검색 대상 (title, contents...)
			$args->search_keyword = Context::get('search_keyword'); ///< 검색어

			$args->module_srl = Context::get('module_srl');

			$oDocumentModel = &getModel('document');
			$output = $oDocumentModel->getDocumentList($args);

			// 템플릿에 쓰기 위해서 document_model::getDocumentList() 의 return object에 있는 값들을 세팅
			$document_srls = array();

			// 이전에 받은 스팸지수를 확인
			foreach($output->data as $key => $val) {
				array_push($document_srls, $val->document_srl);
			}
				
			// 스팸지수를 결과에 포함시킴.
			$args2->document_srls = $document_srls;
			$spam_scores = executeQuery('nspam.getItemList', $args2);

			if (!is_array($spam_scores->data)) $spam_scores = array($spam_scores->data);
			else $spam_scores = $spam_scores->data;
			
			$score_table = array();
			foreach($spam_scores as $key => $val) {
				$score_table[$val->document_srl] = $val->score;
			}
			
			foreach($output->data as $key => $val) {
				$output->data[$key]->score = $score_table[$val->document_srl];
			}

			// 여기까지 정리할 필요 있음.

			Context::set('total_count', $output->total_count);
			Context::set('total_page', $output->total_page);
			Context::set('page', $output->page);
			Context::set('document_list', $output->data);
			Context::set('page_navigation', $output->page_navigation);

			// 템플릿에서 사용할 검색옵션 세팅
			$count_search_option = count($this->search_option);
			for($i=0;$i<$count_search_option;$i++) {
				$search_option[$this->search_option[$i]] = Context::getLang($this->search_option[$i]);
			}
			Context::set('search_option', $search_option);

			$oNspamModel = &getModel('nspam');
			$filters = $oNspamModel->getUseSpamFilters('document');
			$config = $oNspamModel->getNspamPartConfig('document');

			if (!$filters || count(filters) < 1) Context::set('no_spamfilter_specified', 'true');
			if (!$config || $config->use_nspam != 'Y') Context::set('nspam_not_in_use', 'true');
		}

		function dispNspamAdminDocumentDeclaredList(){
		   // 목록을 구하기 위한 옵션
			$args->page = Context::get('page'); ///< 페이지
			$args->list_count = 30; ///< 한페이지에 보여줄 글 수
			$args->page_count = 10; ///< 페이지 네비게이션에 나타날 페이지의 수

			$args->sort_index = 'document_declared.declared_count'; ///< 소팅 값
			$args->order_type = 'desc'; ///< 소팅 정렬 값

			// 목록을 구함
			$declared_output = executeQuery('document.getDeclaredList', $args);

			if($declared_output->data && count($declared_output->data)) {
				$document_list = array();

				$oDocumentModel = &getModel('document');
				foreach($declared_output->data as $key => $document) {
					$document_list[$key] = new documentItem();
					$document_list[$key]->setAttribute($document);
				}
				$declared_output->data = $document_list;
			}


		
			// 템플릿에 쓰기 위해서 document_model::getDocumentList() 의 return object에 있는 값들을 세팅
			Context::set('total_count', $declared_output->total_count);
			Context::set('total_page', $declared_output->total_page);
			Context::set('page', $declared_output->page);
			Context::set('document_list', $declared_output->data);
			Context::set('page_navigation', $declared_output->page_navigation);
		}

		function dispNspamAdminCommentList(){
			// 목록을 구하기 위한 옵션
			$args->page = Context::get('page'); ///< 페이지
			$args->list_count = 30; ///< 한페이지에 보여줄 글 수
			$args->page_count = 10; ///< 페이지 네비게이션에 나타날 페이지의 수
			$args->sort_index = 'list_order'; ///< 소팅 값
			$args->module_srl = Context::get('module_srl');

			// 목록 구함, comment->getCommentList 에서 걍 알아서 다 해버리는 구조이다... (아.. 이거 나쁜 버릇인데.. ㅡ.ㅜ 어쩔수 없다)
			$oCommentModel = &getModel('comment');
			$output = $oCommentModel->getTotalCommentList($args);

			$comment_srls = array();
			$keep_srls = array();
			if($output->data){
				foreach($output->data as $k => $oComment){
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
			}

			$comment_srls2 = array();

			// 이전에 받은 스팸지수를 확인
			foreach($output->data as $key => $val) {
				array_push($comment_srls2, $val->comment_srl);
			}
				
			// 스팸지수를 결과에 포함시킴.
			$args2->document_srls = $comment_srls2;
			$spam_scores = executeQuery('nspam.getItemList', $args2);

			if (!is_array($spam_scores->data)) $spam_scores = array($spam_scores->data);
			else $spam_scores = $spam_scores->data;
			
			$score_table = array();
			foreach($spam_scores as $key => $val) {
				$score_table[$val->document_srl] = $val->score;
			}
			
			foreach($output->data as $key => $val) {
				$output->data[$key]->score = $score_table[$val->comment_srl];
			}

			Context::set('keep_srls',$keep_srls);

			// 템플릿에 쓰기 위해서 comment_model::getTotalCommentList() 의 return object에 있는 값들을 세팅
			Context::set('total_count', $output->total_count);
			Context::set('total_page', $output->total_page);
			Context::set('page', $output->page);
			Context::set('comment_list', $output->data);
			Context::set('page_navigation', $output->page_navigation);

			// 스팸API 사용 여부 및 스팸필터 설정 체크
			$oNspamModel = &getModel('nspam');
			$filters = $oNspamModel->getUseSpamFilters('comment');
			$config = $oNspamModel->getNspamPartConfig('comment');

			if (!$filters || count(filters) < 1) Context::set('no_spamfilter_specified', 'true');
			if (!$config || $config->use_nspam != 'Y') Context::set('nspam_not_in_use', 'true');
		}

		function dispNspamAdminCommentDeclaredList(){
			// 목록을 구하기 위한 옵션
			$args->page = Context::get('page'); ///< 페이지
			$args->list_count = 30; ///< 한페이지에 보여줄 글 수
			$args->page_count = 10; ///< 페이지 네비게이션에 나타날 페이지의 수

			$args->sort_index = 'comment_declared.declared_count'; ///< 소팅 값
			$args->order_type = 'desc'; ///< 소팅 정렬 값

			// 목록을 구함
			$declared_output = executeQuery('comment.getDeclaredList', $args);

			if($declared_output->data && count($declared_output->data)) {
				$comment_list = array();

				$oCommentModel = &getModel('comment');
				foreach($declared_output->data as $key => $comment) {
					$comment_list[$key] = new commentItem();
					$comment_list[$key]->setAttribute($comment);
				}
				$declared_output->data = $comment_list;
			}
		
			// 템플릿에 쓰기 위해서 comment_model::getCommentList() 의 return object에 있는 값들을 세팅
			Context::set('total_count', $declared_output->total_count);
			Context::set('total_page', $declared_output->total_page);
			Context::set('page', $declared_output->page);
			Context::set('comment_list', $declared_output->data);
			Context::set('page_navigation', $declared_output->page_navigation);
		}

		function dispNspamAdminTrackbackList() {

			// 목록을 구하기 위한 옵션
			$args->page = Context::get('page'); ///< 페이지
			$args->list_count = 30; ///< 한페이지에 보여줄 글 수
			$args->page_count = 10; ///< 페이지 네비게이션에 나타날 페이지의 수

			$args->sort_index = 'list_order'; ///< 소팅 값
			$args->module_srl = Context::get('module_srl');

			// 목록 구함
			$oTrackbackAdminModel = &getAdminModel('trackback');
			$output = $oTrackbackAdminModel->getTotalTrackbackList($args);

			// 템플릿에 쓰기 위해서 변수 설정
			Context::set('total_count', $output->total_count);
			Context::set('total_page', $output->total_page);
			Context::set('page', $output->page);
			Context::set('trackback_list', $output->data);
			Context::set('page_navigation', $output->page_navigation);
		}

		function dispNspamAdminKeepList(){
			$obj->page = Context::get('page');
			$obj->by_trigger = 'N';
			
			// 검색 조건 세팅
			$search_keyword = Context::get('search_keyword');
			$search_target = Context::get('search_target');

			switch ($search_target) {

				case 'user_id':
					$obj->user_id = $search_keyword;
					break;
				case 'title_content':
					$obj->title_content = $search_keyword;
					break;
				case 'regdate':
					$obj->regdate = $search_keyword;
					break;
				case 'score':
					$obj->score = $search_keyword;
			}

			$output = executeQueryArray('nspam.getKeepList',$obj);
			if(!$output->toBool()) return $output;
		
			$oDocumentModel = &getModel('document');
			$oCommentModel = &getModel('comment');
			$oTrackbackModel = &getModel('trackback');

			$keep_list = array();
			if($output->data){
				foreach($output->data as $k => $v){
					$data = unserialize($v->data);
					if($v->type=="document"){
						$oDocument = $oDocumentModel->getDocument(0);
						$oDocument->setAttribute($data);
						$v->oDocument = $oDocument;
					}else if($v->type=="comment"){
						$oComment = $oCommentModel->getComment(0);
						$oComment->setAttribute($data);
						$v->oComment = $oComment;
					}else if($v->type="trackback"){
						$v->oTrackback = $data;
					}
					$keep_list[$k] = $v;
				}
			}

			Context::set('keep_list', $keep_list);
			Context::set('page_navigation', $output->page_navigation);
			Context::set('total_count', $output->total_count);
			Context::set('total_page', $output->total_page);
			Context::set('page', $output->page);
 
		}

		function dispNspamAdminBannedList(){
			$obj->page = Context::get('page');
			$obj->by_trigger = 'Y';
			
			// 검색 조건 세팅
			$search_keyword = Context::get('search_keyword');
			$search_target = Context::get('search_target');

			switch ($search_target) {

				case 'user_id':
					$obj->user_id = $search_keyword;
					break;
				case 'title_content':
					$obj->title_content = $search_keyword;
					break;
				case 'regdate':
					$obj->regdate = $search_keyword;
					break;
				case 'score':
					$obj->score = $search_keyword;
			}

			$output = executeQueryArray('nspam.getKeepList',$obj);
			if(!$output->toBool()) return $output;
		
			$oDocumentModel = &getModel('document');
			$oCommentModel = &getModel('comment');
			$oTrackbackModel = &getModel('trackback');

			$keep_list = array();
			if($output->data){
				foreach($output->data as $k => $v){
					$data = unserialize($v->data);
					if($v->type=="document"){
						$oDocument = $oDocumentModel->getDocument(0);
						$oDocument->setAttribute($data);
						$v->oDocument = $oDocument;
					}else if($v->type=="comment"){
						$oComment = $oCommentModel->getComment(0);
						$oComment->setAttribute($data);
						$v->oComment = $oComment;
					}else if($v->type="trackback"){
						$v->oTrackback = $data;
					}
					$keep_list[$k] = $v;
				}
			}

			Context::set('keep_list', $keep_list);
			Context::set('page_navigation', $output->page_navigation);
			Context::set('total_count', $output->total_count);
			Context::set('total_page', $output->total_page);
			Context::set('page', $output->page);
 
		}

		/**
		 * @brief 금지 IP 목록 출력
		 **/
		function dispNspamAdminDeniedIPList() {
			// 등록된 금지 IP 목록을 가져옴
			$oNspamModel = &getModel('nspam');
				
			$search_keyword = Context::get('search_keyword');

			if($search_keyword) {
				$ip_list = $oNspamModel->searchDeniedIP($search_keyword);
			} else {
				$ip_list = $oNspamModel->getDeniedIPList();
			}
			Context::set('ip_list', $ip_list);
		}


		/**
		 * @brief 스팸 모듈에 의해 차단된 회원 목록 출력
		 **/
		function dispNspamAdminDeniedMemberList() {

			$oNspamModel = &getModel('nspam');
			
			//$search_keyword = Context::get('search_keyword');
			//$search_target = Context::get('search_target');

			$denied_member_list = $oNspamModel->getMemberListDeniedByNspam();

			Context::set('denied_member_list', $denied_member_list);
		}


	}
?>
