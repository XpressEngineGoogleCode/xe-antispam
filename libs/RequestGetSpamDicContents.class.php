<?PHP
class RequestGetSpamDicContents extends RequestSpamApi{

	function RequestGetSpamDicContents(){
		$this->add('method','getSpamDicContents');
	}

	function setId($id){
		$this->add('id',$id);
	}

	function setPage($page) {
		$this->add('page', $page);
	}

	function setPer($per) {
		$this->add('per', $per);
	}

}
?>
