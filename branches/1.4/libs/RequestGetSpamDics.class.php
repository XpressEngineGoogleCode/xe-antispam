<?PHP
class RequestGetSpamDics extends RequestSpamApi{

	function RequestGetSpamDics(){
		$this->add('method','getSpamDics');
	}

	function setPage($page=1){
		$this->add('page',$page);
	}

	function setPer($per=10){
		$this->add('per',$per);
	}

	function setId($id){
		$this->add('id',$id);
	}

	function searchName($text){
		$this->add('search_target','name');
		$this->add('search_text',$text);
	}

	function searchDescription($text){
		$this->add('search_target','description');
		$this->add('search_text',$text);
	}
}
?>
