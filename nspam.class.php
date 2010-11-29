<?php
	/**
	 * @class  nspam
	 * @author nhn (developers@xpressengine.com)
	 * @brief  nspam high class
	 **/

	require_once(_XE_PATH_.'modules/nspam/libs/RequestSpamApi.class.php');
	require_once(_XE_PATH_.'modules/nspam/libs/RequestGetSpamFilters.class.php');
	require_once(_XE_PATH_.'modules/nspam/libs/RequestGetSpamDics.class.php');
	require_once(_XE_PATH_.'modules/nspam/libs/RequestGetSpamScores.class.php');
	require_once(_XE_PATH_.'modules/nspam/libs/RequestPutSpamContents.class.php');

	class nspam extends ModuleObject {

		function moduleInstall() {
			// action forward에 등록 (관리자 모드에서 사용하기 위함)
			$oModuleController = &getController('module');

			$oModuleController->insertTrigger('document.insertDocument', 'nspam', 'controller', 'triggerInsertDocument', 'before');
			$oModuleController->insertTrigger('comment.insertComment', 'nspam', 'controller', 'triggerInsertComment', 'before');
			$oModuleController->insertTrigger('trackback.insertTrackback', 'nspam', 'controller', 'triggerInsertTrackback', 'before');

			$oModuleController->insertTrigger('comment.updateComment', 'nspam', 'controller', 'triggerInsertComment', 'before');
			$oModuleController->insertTrigger('document.updateDocument', 'nspam', 'controller', 'triggerInsertDocument', 'before');

			$oModuleController->insertTrigger('comment.deleteComment', 'nspam', 'controller', 'triggerCommentDelete', 'after');
			$oModuleController->insertTrigger('document.deleteDocument', 'nspam', 'controller', 'triggerDocumentDelete', 'after');
			$oModuleController->insertTrigger('trackback.deleteTrackback', 'nspam', 'controller', 'triggerDeleteTrackback', 'after');

			return new Object();
		}

		function checkUpdate() {
			$oDB = &DB::getInstance();
			$oModuleModel = &getModel('module');

			if(!$oModuleModel->getTrigger('document.insertDocument', 'nspam', 'controller', 'triggerInsertDocument', 'before')) return true;
			if(!$oModuleModel->getTrigger('comment.insertComment', 'nspam', 'controller', 'triggerInsertComment', 'before')) return true;
			if(!$oModuleModel->getTrigger('trackback.insertTrackback', 'nspam', 'controller', 'triggerInsertTrackback', 'before')) return true;

			if(!$oModuleModel->getTrigger('comment.updateComment', 'nspam', 'controller', 'triggerInsertComment', 'before')) return true;
			if(!$oModuleModel->getTrigger('document.updateDocument', 'nspam', 'controller', 'triggerInsertDocument', 'before')) return true;

			if(!$oModuleModel->getTrigger('comment.deleteComment', 'nspam', 'controller', 'triggerCommentDelete', 'after')) return true;
			if(!$oModuleModel->getTrigger('document.deleteDocument', 'nspam', 'controller', 'triggerDocumentDelete', 'after')) return true;
			if(!$oModuleModel->getTrigger('trackback.deleteTrackback', 'nspam', 'controller', 'triggerDeleteTrackback', 'after')) return true;

			// 2010. 11. 09 보관 글 목록에 사전 정보 추가 사항 체크.
			if (!$oDB->isColumnExists("nspam_keep", "detected")) return true;
			if (!$oDB->isColumnExists("nspam_keep", "dict_id")) return true;
			if (!$oDB->isColumnExists("nspam_keep", "spam_string")) return true;
			if (!$oDB->isColumnExists("nspam_keep", "score")) return true;
			if (!$oDB->isColumnExists("nspam_denied_ip", "warn_count")) return true;

			// 2010. 11. 26 모듈차단회원 목록 및 보관함 정보 추가 사항 체크.
			if (!$oDB->isTableExists("nspam_denied_member")) return true;
			if (!$oDB->isColumnExists("nspam_keep", "by_trigger")) return true;

			// 2010. 11. 29 모듈차단회원 목록 및 문서/댓글목록 정보 추가 체크.
			if (!$oDB->isTableExists("nspam_item_list")) return true;
			if (!$oDB->isColumnExists("nspam_denied_member", "detected")) return true;
			if (!$oDB->isColumnExists("nspam_denied_member", "dict_id")) return true;
			if (!$oDB->isColumnExists("nspam_denied_member", "spam_string")) return true;
			if (!$oDB->isColumnExists("nspam_denied_member", "score")) return true;

			return false;
		}

		function moduleUpdate() {
			$oDB = &DB::getInstance();
			$oModuleModel = &getModel('module');
			$oModuleController = &getController('module');

			if(!$oModuleModel->getTrigger('document.insertDocument', 'nspam', 'controller', 'triggerInsertDocument', 'before'))
				$oModuleController->insertTrigger('document.insertDocument', 'nspam', 'controller', 'triggerInsertDocument', 'before');
			if(!$oModuleModel->getTrigger('comment.insertComment', 'nspam', 'controller', 'triggerInsertComment', 'before'))
				$oModuleController->insertTrigger('comment.insertComment', 'nspam', 'controller', 'triggerInsertComment', 'before');
			if(!$oModuleModel->getTrigger('trackback.insertTrackback', 'nspam', 'controller', 'triggerInsertTrackback', 'before'))
				$oModuleController->insertTrigger('trackback.insertTrackback', 'nspam', 'controller', 'triggerInsertTrackback', 'before');

			if(!$oModuleModel->getTrigger('comment.updateComment', 'nspam', 'controller', 'triggerInsertComment', 'before')){
				$oModuleController->insertTrigger('comment.updateComment', 'nspam', 'controller', 'triggerInsertComment', 'before');
			}
			if(!$oModuleModel->getTrigger('document.updateDocument', 'nspam', 'controller', 'triggerInsertDocument', 'before')){
				$oModuleController->insertTrigger('document.updateDocument', 'nspam', 'controller', 'triggerInsertDocument', 'before');
			}
			if(!$oModuleModel->getTrigger('comment.deleteComment', 'nspam', 'controller', 'triggerCommentDelete', 'after')){
				$oModuleController->insertTrigger('comment.deleteComment', 'nspam', 'controller', 'triggerCommentDelete', 'after');
			}
			if(!$oModuleModel->getTrigger('document.deleteDocument', 'nspam', 'controller', 'triggerDocumentDelete', 'after')){
				$oModuleController->insertTrigger('document.deleteDocument', 'nspam', 'controller', 'triggerDocumentDelete', 'after');
			}
			if(!$oModuleModel->getTrigger('trackback.deleteTrackback', 'nspam', 'controller', 'triggerDeleteTrackback', 'after')){
				$oModuleController->insertTrigger('trackback.deleteTrackback', 'nspam', 'controller', 'triggerDeleteTrackback', 'after');
			}


			 
			// 2010. 11. 09 보관 글 목록에 사전 정보 추가 사항 체크.
			if (!$oDB->isColumnExists("nspam_keep", "detected")) $oDB->addColumn('nspam_keep', "detected", "char", 1);
			if (!$oDB->isColumnExists("nspam_keep", "dict_id"))  $oDB->addColumn('nspam_keep', "dict_id", "varchar", 10);
			if (!$oDB->isColumnExists("nspam_keep", "spam_string"))  $oDB->addColumn('nspam_keep', "spam_string", "text");
			if (!$oDB->isColumnExists("nspam_keep", "score")) $oDB->addColumn('nspam_keep', "score", "number", 3);
			if (!$oDB->isColumnExists("nspam_denied_ip", "warn_count")) $oDB->addColumn('nspam_denied_ip','warn_count','number',3, 3 ,true);


			// 2010. 11. 26 모듈차단회원 목록 및 보관함 정보 추가사항 체크.
			if (!$oDB->isTableExists("nspam_denied_member")) $oDB->createTableByXmlFile("nspam_denied_member");
			if (!$oDB->isColumnExists("nspam_keep", "by_trigger")) $oDB->addColumn('nspam_keep', "by_trigger", "char", 1);

			// 2010. 11. 29. 모듈차단회원 목록 및 문서 / 댓글 목록 정보 추가 체크.
			if (!$oDB->isTableExists("nspam_denied_member")) $oDB->createTableByXmlFile("nspam_item_list");
			if (!$oDB->isColumnExists("nspam_denied_member", "detected")) $oDB->addColumn('nspam_denied_member', "detected", "char", 1);
			if (!$oDB->isColumnExists("nspam_denied_member", "dict_id"))  $oDB->addColumn('nspam_denied_member', "dict_id", "varchar", 10);
			if (!$oDB->isColumnExists("nspam_denied_member", "spam_string"))  $oDB->addColumn('nspam_denied_member', "spam_string", "text");
			if (!$oDB->isColumnExists("nspam_denied_member", "score")) $oDB->addColumn('nspam_denied_member', "score", "number", 3);

			return new Object(0, 'success_updated');
		}

		function recompileCache() {
		}
	}
?>
