<?php
class ramdon_code{
	public static function genRadomCode() {
		$random_code = hash('adler32', mt_rand());
		return $random_code;
	}
	public static function genRadomToken() {
		$random_Token = hash('adler32', mt_rand());
		return $random_Token;
	}
}
/**
 * Don't include any model data structure in applicant
 */
class applicant {
	public $id;
	public $db;
	function __construct($db){
		$this->db = $db;
	}
	private function PDOKey($hash,$replace){
		$condition = preg_replace('/(.*)/',$replace,array_keys($hash),1);
		return $condition;
	}
	public function getMemcacheValue($key){
			$mem = new Memcache;
			$mem->connect(MEM_HOST,MEM_PORT);
			return $mem->get($key);
		}
	public function verify($table,$authArray){
		$sql = "SELECT * FROM `$table` WHERE ";
		$filter=implode(" AND ", $this->PDOKey($authArray,'`${1}`=?'));
		$sql .= $filter;
		$sth = $this->db->prepare($sql);
		$sth->execute(array_values($authArray));

		$count = $sth->rowCount();
		if($count != 1){
			return 0;
		}else{
			return 1;
		}
	}
	public function add($table,$addArray){
		$addKey = implode(",", $this->PDOKey($addArray,'`${1}`'));
		$addValue = implode(",", $this->PDOKey($addArray,'?'));
		$sql = "INSERT INTO $table ($addKey) VALUES ($addValue)";
		$sth = $this->db->prepare($sql);
		$sth->execute(array_values($addArray));    
		//echo $this->db->lastinsertid(); 
	}
	public function update($table,$updateArray,$filterArray){
		$filter=implode(" AND ", $this->PDOKey($filterArray,'`${1}`=?'));
		$value =implode(" , ", $this->PDOKey($updateArray,'`${1}`=?'));
		$sql = "UPDATE `$table` SET $value WHERE $filter";    
		$sth = $this->db->prepare($sql);    
		$dataArray =  array_merge(array_values($updateArray), array_values($filterArray));
		$sth->execute($dataArray); 
	}
	public function select($table,$filterArray){
		$filter=implode(" AND ", $this->PDOKey($filterArray,'`${1}`=?'));
		$sql = "SELECT * FROM `$table` WHERE ".$filter;
		$sth = $this->db->prepare($sql);    
		$sth->execute(array_values($filterArray));    
		$row = $sth->fetch(PDO::FETCH_ASSOC);
		return $row;
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
		$redirect_uri = $getArray['redirect_uri'];
		$result = $this->verify('clients',array('client_id'=>$client_id,'redirect_uri'=>$redirect_uri));
		return $result;
	}
/**
 * verifyAccessTokenRequest need verify below items:
 * client_id 		string 	AppKey
 * client_secret 	string 	AppSecret
 * grant_type 		string 	='authorization_code'
 * 
 * code 		string 	authorize code
 * redirect_uri 	string 	 
 *
 * step-1:
 * verify memcache key $random_code with client_id/redirect_uri/code
 * step-2:
 * verify clients table with client_id/client_secret/redirect_uri
*/
	public function verifyAccessTokenRequest($postArray){
		$step1VerifyArray = array('client_id','redirect_uri');
		$mem = new Memcache;
		$mem->connect(MEM_HOST,MEM_PORT);
		
		if($hash = $mem->get([$postArray['code']])){
			$cachedArray = json_decode($hash[$postArray['code']],true);
			foreach($step1VerifyArray as $k){
				if($postArray[$k] !== $cachedArray[$k]){
					echo "$k";
					return 0;
				}
			}
			$step2Array = array(
				'client_id'=>$postArray['client_id'],
				'client_secret'=>$postArray['client_secret'],
				'redirect_uri'=>$postArray['redirect_uri'],
			);
			$result = $this->verify('clients',$step2Array);
			return $result;
		}else{
			return 0;
		}
	}
	public function getPrivilege($getArray){
		$privilegeArray = array(
			'read'	=>	1,
			'write'	=>	0,
			'other'	=>	1,
		);
		return $privilegeArray;
	}
	public function authorizeCode($getArray,$user){
		$getArray['user'] = $user;
		$random_code = ramdon_code::genRadomCode();

		//store in database or cache,such as memcache,mysql...etc
		$mem = new Memcache;
		$mem->connect(MEM_HOST, MEM_PORT);
		$key = $random_code;
		$val = json_encode($getArray);
		$mem->set($key, $val, 0, 120);

		return $random_code;
	}
	public function getRedirectUrl($getArray,$random_code){
		$url = sprintf("%s?code=%s",$getArray['redirect_uri'],$random_code);

		if(isset($getArray['stat'])){
			$url.= sprintf("%s&stat=%s",$url,$getArray['stat']);
		}
		return $url;
	}

	public function authorizeToken($code){
		$hashJson=$this->getMemcacheValue($code);
		$hashArray = json_decode($hashJson,true);
		$record = array(
			'token_id' 	  => ramdon_code::genRadomCode(),
			'token_uid'	  => $hashArray['user'],
			'token_client_id' => $hashArray['client_id'],
			'token_scope' 	  => 'scope123',
			'token_create_at' => time(),
			'token_expire_in' => time()+120,
		);
		$checkArray = array(
			'token_uid'	  => $hashArray['user'],
			'token_client_id' => $hashArray['client_id'],
		);

		if(!$this->verify('access_tokens',$checkArray)){
			$this->add('access_tokens',$record);
		}else{
			$recordArray = $this->select('access_tokens',$checkArray);
			if($record['token_create_at'] > $recordArray['token_expire_in']){
				$updateRecord = array(
					'token_id' 	  => $record['token_id'],
					'token_scope' 	  => $record['token_scope'],
					'token_create_at' => $record['token_create_at'],
					'token_expire_in' => $record['token_expire_in'],
				);
				$this->update('access_tokens',$updateRecord,$checkArray);
			}else{
				$record['token_id'] = $recordArray['token_id'];
				$record['token_expire_in'] = $recordArray['token_expire_in'];
			}
		}
/**
 * feedback data structure with POST method
 *       "access_token": "ACCESS_TOKEN",
 *       "expires_in": 123456789,
 *       "uid":"77167680"
 * 
 */
		$retData= array(
			"access_token"	=> $record['token_id'],
			"expires_in"	=> $record['token_expire_in'],
			"uid"		=> $record['token_uid'],
		);
		return json_encode($retData);
	}
}
?>
