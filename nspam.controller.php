<?PHP
	/**
	 * @class  nspamController
	 * @author nhn (developers@xpressengine.com)
	 * @brief  nspam 모듈의 controller class
	 **/

	class nspamController extends nspam{

		function init(){
		}

		function triggerDeleteDocument(&$obj) {
			$robj->nspam_keep_srl = $obj->document_srl;
			$output = executeQuery('nspam.deleteKeep',$robj);
		}

		function triggerDeleteComment(&$obj) {
			$robj->nspam_keep_srl = $obj->comment_srl;
			$output = executeQuery('nspam.deleteKeep',$robj);
		}

		function triggerDeleteTrackback(&$obj) {
			$robj->nspam_keep_srl = $obj->trackback_srl;
			$output = executeQuery('nspam.deleteKeep',$robj);
		}

		// 글 추가시 트리거
		function triggerInsertDocument(&$obj) {
			// 로그인 여부, 로그인 정보, 권한 유무 체크
			$is_logged = Context::get('is_logged');
			$logged_info = Context::get('logged_info');
			$grant = Context::get('grant');

			// 로그인 되어 있을 경우 관리자 여부를 체크
			if($is_logged) {
				if($logged_info->is_admin == 'Y') return new Object();
				if($grant->manager) return new Object();

				$obj->member_srl = $logged_info->member_srl;
				$obj->nick_name = $logged_info->nick_name;
				$obj->email_address = $logged_info->email_address;
				$obj->homepage = $logged_info->homepage;
			}

			// pass
			if($obj->_from_nspam_keep_) return new Object();
			unset($obj->_from_nspam_keep_);

			$oNspamModel = &getModel('nspam');
			if(!$oNspamModel->useSpam($obj->module_srl,'document')) return new Object();

			// ip가 금지되어 있는 경우를 체크
			$oNspamModel = &getModel('nspam');
			$output = $oNspamModel->isDeniedIP();
			if(!$output->toBool()) return $output;

			$vars = new stdClass;
			$vars->content = $obj->content;
			$vars->title = $obj->title;

			// 스팸지수를 SIS서버로부터 가져옴
			$result = $this->getSpamScore($vars,'document');

			// 설정된 액션을 실행
			$output = $this->doSpamProcess($obj, $result, 'document');

			return $output;
		}


		/**
		 * @brief 댓글 추가 시에 실행되는 트리거
		 */
		function triggerInsertComment(&$obj) {
			// 로그인 여부, 로그인 정보, 권한 유무 체크
			$is_logged = Context::get('is_logged');
			$logged_info = Context::get('logged_info');
			$grant = Context::get('grant');

			// 로그인 되어 있을 경우 관리자 여부를 체크
			if($is_logged) {
				if($logged_info->is_admin == 'Y') return new Object();
				if($grant->manager) return new Object();

				$obj->member_srl = $logged_info->member_srl;
				$obj->nick_name = $logged_info->nick_name;
				$obj->email_address = $logged_info->email_address;
				$obj->homepage = $logged_info->homepage;

			}
			
			// pass
			if($obj->_from_nspam_keep_) return new Object();
			unset($obj->_from_nspam_keep_);

			// ip가 금지되어 있는 경우를 체크
			$oNspamModel = &getModel('nspam');
			$output = $oNspamModel->isDeniedIP();
			if(!$output->toBool()) return $output;

			$oModuleModel = &getModel('module');
			$module_info = $oModuleModel->getModuleInfoByDocumentSrl($obj->document_srl);
			$module_srl = $module_info->module_srl;

			$oNspamModel = &getModel('nspam');
			if(!$oNspamModel->useSpam($module_srl,'comment')) return new Object();

			$vars = new stdClass;
			$vars->content = $obj->content;

			// 스팸지수를 SIS서버로부터 가져옴
			$result = $this->getSpamScore($vars,'comment');

			// 설정된 액션을 실행
			$output = $this->doSpamProcess($obj, $result,'comment');

			return $output;
		}

		/**
		 * @brief 트랙백 추가 시 실행되는 트리거
		 */
		function triggerInsertTrackback(&$obj) {

			// pass
			if($obj->_from_nspam_keep_) return new Object();
			unset($obj->_from_nspam_keep_);

			$oNspamModel = &getModel('nspam');
			if(!$oNspamModel->useSpam($obj->module_srl,'trackback')) return new Object();

			// 로그인 되어 있을 경우 관리자 여부를 체크
			if($is_logged) {
				if($logged_info->is_admin == 'Y') return new Object();
				if($grant->manager) return new Object();

				$obj->member_srl = $logged_info->member_srl;
				$obj->nick_name = $logged_info->nick_name;
				$obj->email_address = $logged_info->email_address;
				$obj->homepage = $logged_info->homepage;
			}

			// ip가 금지되어 있는 경우를 체크
			$output = $oNspamModel->isDeniedIP();
			if(!$output->toBool()) return $output;

			$vars = new stdClass;
			$vars->title = $obj->title;
			$vars->content = $obj->excerpt;

			// 스팸지수를 SIS서버로부터 가져옴
			$result = $this->getSpamScore($vars,'trackback');

			// 설정된 액션을 실행
			$output = $this->doSpamProcess($obj, $result,'trackback');

			return $output;
		}

		/**
		 * @brief 스팸 지수에 따라 act를 구함.
		 */
		function getAction($score, $type){	
			$is_logged = Context::get('is_logged');

			$return = array();
			$oNspamModel = &getModel('nspam');

			$config = $oNspamModel->getNspamPartConfig($type);

			if(!$config) return $return;

			
			// 로그인되어 있으면 계정 차단, 비로그인이면 아이피 차단
			if ($is_logged) {
				if ($config->score_deny_user <= $score) 
					array_push($return,'denied_user');

			} else {
				if ($config->score_denied_ip <= $score) 
					array_push($return, 'denied_ip');
			}
			if ($config->score_trash_content <= $score) array_push($return, 'trash');

			return $return;
		}

		/**
		 * @brief spam api 를 통해 content 에 대한 스팸 지수를 가져옴
		 */
		function getSpamScore($vars, $type){
			$content = $vars->content;
			$title = $vars->title;
			$ip = $_SERVER['REMOTE_ADDR'];
			$time = date('Y-m-d H:i:s');

			$oNspamModel = &getModel('nspam');

			$config = $oNspamModel->getNspamPartConfig($type);

			$filters = $oNspamModel->getUseSpamFilters($type);
			$dics = $oNspamModel->getUseSpamDics($type);

			$oReq = new RequestGetSpamScores();
			if(count($filters)) $oReq->addSpamFilters($filters);
			if(count($dics)) $oReq->addSpamDics($dics);
			$oReq->addContent(1,$content,$title,$ip,$time);

			$output = $oReq->request();

			if(!$output || $output->error != 0) return false;
			if(!$output->scores || !$output->scores->item) return false;

			return $output->scores->item[0];
		}


		/**
		 * @brief 설정된 액션을 스팸지수에따라 실행
		 **/
		function doSpamProcess($obj, $result, $type){
			$action_list = $this->getAction($result->score, $type);

			foreach($action_list as $k => $act){
				if($act=='denied_ip'){
				  $this->giveWarning();
				}else if($act=='denied_user'){
					$this->denyUser();
				}else if($act=='trash'){
					$this->trashObject($obj, $type, $result);
					return new Object(-1,'msg_trash_content');
				}
			}
			
			return new Object();
		}


		function doSpamBatchProcess($obj, $result, $type, $author_srl) {

			$action_list = $this->getAction($result->score, $type);

			foreach($action_list as $k => $act){
				if($act=='denied_ip'){
				  $this->giveWarning();
				}else if($act=='denied_user'){
					$this->denyUser($author_srl);
				}else if($act=='trash'){
					$this->trashObject($obj, $type, $result);
				}
			}
			return new Object();
		}
		
		
		/**
		 * @brief IP에 대해 경고 회수를 1회 추가함.
		 **/
		function giveWarning() {
			// ip 가 경고 이력이 있는지 체크
			$ipaddress = $_SERVER['REMOTE_ADDR'];

			$oNspamModel = &getModel('nspam');
			$output = $oNspamModel->hasWarning($ipaddress);

			if ($output) {
				$this->increaseWarning();
			} else {
				$this->giveFirstWarning();
			}
		}

		/**
		 *
		 **/		
		function increaseWarning($ipaddress=null) {
			if (!$ipaddress) $ipaddress = $_SERVER['REMOTE_ADDR'];
	
			$args->ipaddress = $ipaddress;
			$output = executeQuery('nspam.increaseWarning', $args);
			
			return $output;	
		}
		

		/**
		 *
		 **/		
		function giveFirstWarning($ipaddress=null) {
			if (!$ipaddress) $ipaddress = $_SERVER['REMOTE_ADDR'];

			$args->ipaddress = $ipaddress;
			$output = executeQuery('nspam.firstWarning', $args);

			return $output;	
		}

		/**
		 * @brief IP 등록
		 * 등록된 IP는 스패머로 간주
		 **/
		function deniedIP($ipaddress=null) {
			if(!$ipaddress) $ipaddress = $_SERVER['REMOTE_ADDR'];

			$oNspamModel = &getModel('nspam');
			$output = $oNspamModel->hasWarning($ipaddress);


			if ($output) {
				$args->ipaddress = $ipaddress;
				$args->warn_count = 3;
				$output = executeQuery('nspam.increaseWarning', $args);
			} else {
				$args->ipaddress = $ipaddress;
				$output = executeQuery('nspam.insertDeniedIP', $args);
			}

			return $output;
		}

		/**
		 * @brief 로그인 된 사용자일 경우 사용자 차단 처리, 사용자가 지정되어 있을 경우 해당 사용자를 차단.
		 */
		function denyUser($member_srl=NULL) {
			$is_logged = Context::get('is_logged');

			if  ($is_logged) {
				$vars = new stdClass;
				$target_user = NULL;

				// member_srl 이 지정된 경우 해당 사용자 차단
				// 아닌 경우, 로그인된 사용자를 차단.
				if (!$member_srl) {
					$logged_info = Context::get('logged_info');
					$target_user = $logged_info->member_srl;
				} else {
					$target_user = $member_srl;
				}
				$vars->member_srl = $target_user;

				// 차단 대상 회원이 관리자이거나 이미 차단당했다면 차단하지 않음
				$usr_info = executeQuery('member.getMemberInfoByMemberSrl', $vars);
				if ($usr_info->data->is_admin == 'Y' || $usr_info->data->denied == 'Y') return new Object();

				// 차단 조치
				$output = executeQuery('nspam.updateDeniedMember',$vars);

				// 차단 대상 회원 description 에 차단 사유 및 시간을 등록
				$date = date("Y.m.d G:i:s");
				$new_desc = $date."에 스팸등록으로 차단되었습니다.\n".$usr_info->data->description;

				unset($vars);
				$vars = new stdClass;
				$vars->member_srl = $target_user;
				$vars->description = $new_desc;
				$opt = executeQuery('member.updateMember', $vars);
				return $output;
			}
			return new Object();
		}


		/**
		 * @brief 스팸으로 판정된 글/댓글을 보관함으로 옮김.
		 */
		function trashObject($obj, $type='document', $result=NULL){

			if($type=='document'){
				if($obj->document_srl){
					$oDocumentModel = &getModel('document');
					$oDocument = $oDocumentModel->getDocument($obj->document_srl);

					// 이미 존재 하는 글이면 
					if($oDocument->isExists()){
						$obj = $oDocument->gets('document_srl','module_srl','category_srl','lang_code','is_notice','is_secret','title','title_bold','title_color','content','readed_count','voted_count','blamed_count','comment_count','trackback_count','uploaded_count','password','user_id','user_name','nick_name','member_srl','email_address	homepage','tags','extra_vars','regdate','last_update		last_updater	ipaddress	list_order	update_order	allow_comment','lock_comment','allow_trackback','notify_message');

						$var = new stdClass;
						$var->document_srl = $obj->document_srl;
						$output = executeQuery('document.deleteDocument',$var);
					}
				}else{
					$obj->document_srl = getNextSequence();
				}

				if(!$obj->list_order) $obj->update_order = $obj->list_order = $obj->document_srl* -1;
				
				$vars = new stdClass;
				$vars->nspam_keep_srl = $obj->document_srl;
				$vars->type = $type;
				$vars->data = serialize($obj);
				// 스팸사전에서 필터된 결과 저장
				if ($result->dictionary_result && $result->dictionary_result != "") {
					$vars->detected = $result->dictionary_result->detect == 'true' ? 'Y':'N';
					$vars->dict_id = $result->dictionary_result->dictionary_id;
					$vars->spam_string = $result->dictionary_result->spam_string;
				}
				$vars->score = $result->score;
				
				$output = executeQuery('nspam.insertKeep',$vars);

			}else if($type=='comment'){
				if($obj->comment_srl){
					$oCommentModel = &getModel('comment');
					$oComment = $oCommentModel->getComment($obj->comment_srl);
					// 이미 댓글이 존재하는 경우
					if($oComment->isExists()){
						$obj = $oComment->gets('comment_srl','module_srl','document_srl','parent_srl','is_secret','content','voted_count','blamed_count','notify_message','password','user_id','user_name','nick_name','member_srl','email_address','homepage','uploaded_count','regdate','last_update','ipaddress','list_order');

						// 자식 댓글이 있으면 지울 수 없고, 내용 업데이트
						if($oCommentModel->getChildCommentCount($obj->comment_srl) > 0){
							$var = clone($obj);
							$var->member_srl = 0;
							$var->contnet = sprintf('<p class="spam">%</p>',Context::getLang('msg_spam_comment'));	
							$output = executeQuery('comment.updateComment',$var);

						}else{
							$var = new stdClass;
							$var->comment_srl = $obj->comment_srl;

							$output = executeQuery('comment.deleteComment',$var);
						}

					}
				}else{
					$obj->comment_srl = getNextSequence();
				}

				if(!$obj->list_order) $obj->list_order = $obj->comment_srl* -1;

				$vars = new stdClass;
				$vars->nspam_keep_srl = $obj->comment_srl;
				$vars->type = $type;
				$vars->data = serialize($obj);
				
				// 스팸사전에서 필터된 결과 저장
				if ($result->dictionary_result && $result->dictionary_result != "") {
					$vars->detected = $result->dictionary_result->detect == 'true' ? 'Y':'N';
					$vars->dict_id = $result->dictionary_result->dictionary_id;
					$vars->spam_string = $result->dictionary_result->spam_string;
				}
				$vars->score = $result->score;
				$output = executeQuery('nspam.insertKeep',$vars);

			}else if($type=='trackback'){
				if($obj->trackback_srl){
				
					$oTrackbackModel = &getModel('trackback');
					$output = $oTrackbackModel->getTrackback($obj->trackback_srl);

					// 트랙백이 존재하는 경우
					if($output->data){
						$obj = clone($output->data);
						$output = executeQuery('trackback.deleteTrackback',$obj);
					}
				}else{
					if(!$obj->trackback_srl) $obj->trackback_srl = getNextSequence();
				}

				if(!$obj->list_order) $obj->list_order = $obj->trackback_srl* -1;

				$vars = new stdClass;
				$vars->nspam_keep_srl = $obj->trackback_srl;
				$vars->type = 'comment';
				$vars->data = serialize($obj);

				// 스팸사전에서 필터된 결과 저장
				if ($result->dictionary_result && $result->dictionary_result != "") {
					$vars->detected = $result->dictionary_result->detect == 'true' ? 'Y':'N';
					$vars->dict_id = $result->dictionary_result->dictionary_id;
					$vars->spam_string = $result->dictionary_result->spam_string;
				}
				$vars->score = $result->score;
				$output = executeQuery('nspam.insertKeep',$vars);
			}

			return $output;
		}
	}
?>
