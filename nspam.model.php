<?php
	/**
	 * @class  nspamModel
	 * @author nhn (developers@xpressengine.com)
	 * @brief  nspam model class
	 **/

	class nspamModel extends nspam {

		function init() {
		}


		/**
		 * @brief API를 통해 스팸사전 목록을 가져옴
		 **/
		function getSpamDics($page=1,$per=10){
			$obj = new stdClass;
			$obj->method = 'getSpamDics';
			$obj->page = $page;
			$obj->per = $per;
		
			$oApi = new requestSpamApi;
			$output = $oApi->request($obj);
			if(!$output || !$output->spamdics) return new Object(-1,'msg_invalid_request');

			return $output;
		}

		/**
		 * @brief API를 통해 스팸필터 목록을 가져옴
		 **/
		function getSpamFilters($page=1,$per=10){
			$obj = new stdClass;
			$obj->method = 'getSpamFilters';
			$obj->page = $page;
			$obj->per = $per;
		
			$oApi = new requestSpamApi;
			$output = $oApi->request($obj);
			if(!$output || !$output->spamdics) return new Object(-1,'msg_invalid_request');

			return $output;
		}


		/**
		 * @brief 등록된 금지 IP의 목록을 return
		 **/
		function getDeniedIPList() {
			$args->warn_count = 3;
			$args->sort_index = "regdate";
			$args->page = Context::get('page')?Context::get('page'):1;
			$output = executeQuery('nspam.getDeniedIPList', $args);
			if(!$output->data) return;
			if(!is_array($output->data)) return array($output->data);
			return $output->data;
		}

		function searchDeniedIP($search_keyword) {
			$args->warn_count = 3;
			$args->search_keyword = $search_keyword;
			$args->sort_index = "regdate";
			$args->page = Context::get('page')?Context::get('page'):1;

			$output = executeQuery('nspam.searchDeniedIP', $args);

			if (!$output->data) return;
			if (!is_array($output->data)) return array($output->data);

			return $output->data;

		}



		/**
		 * @brief 인자로 넘겨진 ipaddress가 금지 ip인지 체크하여 return
		 **/
		function isDeniedIP() {
			$ipaddress = $_SERVER['REMOTE_ADDR'];

			$ip_list = $this->getDeniedIPList();
			if(!count($ip_list)) return new Object();

			$count = count($ip_list);
			$patterns = array();
			for($i=0;$i<$count;$i++) {
				$ip = str_replace('*','',$ip_list[$i]->ipaddress);
				$patterns[] = preg_quote($ip);
			}

			$pattern = '/^('.implode($patterns,'|').')/';

			if(preg_match($pattern, $ipaddress, $matches)) return new Object(-1,'msg_alert_registered_denied_ip');
			 
			return new Object();
		}
		
		
		
		/**
		 * @brief 
		 **/	
		function hasWarning($ipaddress=null) {
			if(!$ipaddress) $ipaddress = $_SERVER['REMOTE_ADDR'];

			$args->ipaddress = $ipaddress;
		
			

			$output = executeQuery('nspam.isWarnedIP', $args);
			if (!$output->data->count) {
				return False;
			}
			return True;
		}
		
  
		/**
		 * @brief
		 **/	  
		function getWarnedIPList() {
			$args->sort_index = "regdate";
			$args->page = Context::get('page')? Context::get('page') : 1;
			$output = executeQuery('nspam.getWarnedIPList', $args);
	
			if (!$output->data) return;
			if (!is_array($output->data)) return array($output->data);

			return $output->data;
		}

		function getNspamConfig(){
			if($this->config) return $this->config;

			$oModuleModel = &getModel('module');
			$config = $oModuleModel->getModuleConfig('nspam');

			if(!$config) return false;
			$this->config = $config;

			return $this->config;
		}

		function getNspamPartConfig($type){
			$config = $this->getNspamConfig();

			return $config->{$type};
		}

		// 모듈에서 nspam 사용 여부 확인
		function useSpam($module_srl,$type='document'){
			$config = $this->getNspamPartConfig($type);

			if(!$config || $config->use_nspam!='Y') return false;

			if(!is_array($config->target_module)) $config->target_module = array();

			if($config->module_apply=='deny'){
				return !in_array($module_srl,$config->target_module);
			}else if($part_config->module_apply=='allow'){
				return in_array($module_srl,$config->target_module);
			}

			return false;
		}

		function getUseSpamFilters($type){
			$config = $this->getNspamPartConfig($type);

			$filters = array();
			if(is_array($config->use_spamfilters) && count($config->use_spamfilters)>0){
				$filters = array_keys($config->use_spamfilters);
			}

			return $filters;
		}

		function getUseSpamDics($type){
			$config = $this->getNspamPartConfig($type);

			$dics = array();
			if(is_array($nspam_config->use_spamdics) && count($nspam_config->use_spamdics)>0){
				$dics = array_keys($nspam_config->use_spamdics);
			}

			return $dics;
		}

	}
?>
