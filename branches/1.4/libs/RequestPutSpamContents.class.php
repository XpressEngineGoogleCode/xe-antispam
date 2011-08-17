<?PHP
class RequestPutSpamContents extends RequestSpamApi{

	function RequestPutSpamContents(){
		$this->add('method','putSpamContents');

		$contents = new stdClass;
		$contents->item = array();
		$this->add('contents',$contents);
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
