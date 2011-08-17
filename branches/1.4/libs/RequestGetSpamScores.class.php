<?PHP
class RequestGetSpamScores extends RequestSpamApi{

	function RequestGetSpamScores(){
		$this->add('method','getSpamScores');

		$contents = new stdClass;
		$contents->item = array();
		$this->add('contents',$contents);

		$item = new stdClass;
		$item->id = 1;
		$spamfilters = new stdClass;
		$spamfilters->item = array($item);

		$this->add('spamfilters',$spamfilters);

		$spamdics = new stdClass;
		$spamdics->item = array();
		$this->add('spamdics',$spamdics);
	}

	function addSpamFilters($ids){
		foreach($ids as $k=>$v){
			if($v) $this->addSpamFilter($v);
		}
	}

	function addSpamDics($ids){
		foreach($ids as $k=>$v){
			if($v) $this->addSpamDic($v);
		}
	}

	function addSpamFilter($spamfilter_id){
		$obj = new stdClass;
		$obj->id = $spamfilter_id;

		$spamfilters = $this->get('spamfilters');
		array_push($spamfilters->item,$obj);
		$this->add('spamfilters',$spamfilters);
	}

	function addSpamDic($spamdic_id){
		$obj = new stdClass;
		$obj->id = $spamdic_id;

		$spamdics = $this->get('spamdics');
		array_push($spamdics->item,$obj);
		$this->add('spamdics',$spamdics);
	}

	function addContent($content_id,$content,$title=null,$ip=null,$pubdate=null){
		if(!$content_id || !$content) return false;
	
		$obj = new stdClass;
		$obj->id = $content_id;
		$obj->title = $title;
		$obj->content = $content;
		$obj->ip = $ip;
		$obj->pubdate = $pubdate;

		$contents = $this->get('contents');
		array_push($contents->item,$obj);
		$this->add('contents',$contents);
	}
}

?>
