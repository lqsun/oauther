<?php
class applicant {
	public $id;
	public $db;
	function __construct($db){
		$this->db = $db;
	}
	public function verify($table,$authArray){
		$filter = "SELECT * FROM $table WHERE 1=1 ";
		foreach($authArray as $k => $v){
			$filter .= sprintf(" AND %s=? ",$k);
			$data[] = $v;
		}

		$sth = $this->db->prepare($filter);
		$sth->execute($data);

		$count = $sth->rowCount();
		if($count != 1){
			return 0;
		}else{
			return 1;
		}
	}
}
class user extends applicant {
	public function verifyPasswd($getArray){
		$name = $getArray['userName'];
		$pwd  = $getArray['userPassword'];
		$result = $this->verify('users',array('user_name'=>$name,'user_password_hash'=>$pwd));
		return $result;
	}
}
class client extends applicant{
	private $autoPar = array(
		'client_id'	=>1,	//appKey
		'response_type'	=>1,
		'redirect_uri'	=>1,
		'scope'		=>0,
		'stat'		=>0,
		'display'	=>0,
		'forcelogin'	=>0,
		'language'	=>0,
	);
	public function verifyGet($getArray){
		foreach($this->autoPar as $k => $v){
			if(!isset($getArray[$k]) && $v){
				return 0;
			}
		}
		return 1;
	}
	public function verifyAppKey($getArray){
		$client_id = $getArray['client_id'];
		$client_redirect_uri = $getArray['redirect_uri'];
		$result = $this->verify('clients',array('client_id'=>$client_id,'client_redirect_uri'=>$client_redirect_uri));
		return $result;
	}
	public function getPrivilege($getArray){
		$privilegeArray = array(
			'read'	=>	1,
			'write'	=>	0,
			'other'	=>	1,
		);
		return $privilegeArray;
	}
	public function redirectUrl($getArray){
		$url = sprintf("%s?code=%s",$getArray['redirect_uri'],'test');

		if(isset($getArray['stat'])){
			$url.= sprintf("%s&stat=%s",$url,$getArray['stat']);
		}
		return $url;
	}
}
?>
