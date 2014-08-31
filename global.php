<?php
class authorize {
	private $autoPar = array(
		'client_id'	=>1,
		'response_type'	=>1,
		'redirect_uri'	=>1,
		'scope'		=>0,
		'stat'		=>0,
		'display'	=>0,
		'forcelogin'	=>0,
		'language'	=>0,
	);
	private $getArray;
	function __construct ($getArray){
		$this->getArray = $getArray;
	}
	public function verifyGet(){
		foreach($this->autoPar as $k => $v){
			if(!isset($this->getArray[$k]) && $v){
				return 0;
			}
		}
		return 1;
	}
	public function redirect(){
		$url = sprintf("%s?code=%s",$this->getArray['redirect_uri'],'test');
		if(isset($this->getArray['stat'])){
			$url.= sprintf("%s&stat=%s",$url,$this->getArray['stat']);
		}
		Header("HTTP/1.1 303 See Other");
		echo "<META HTTP-EQUIV='Refresh' CONTENT='0;URL=$url'>";
		
	}
}
?>
