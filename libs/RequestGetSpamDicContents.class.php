<?PHP
class RequestGetSpamDicContents extends RequestSpamApi{

	function RequestGetSpamDicContents(){
		$this->add('method','getSpamDicContents');
	}

	function setId($id){
		$this->add('id',$id);
	}

}
?>
