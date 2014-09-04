<!--
client_id 	string 	AppKey
client_secret 	string 	AppSecret
grant_type 	string 	='authorization_code'

code 		string 	authorize code
redirect_uri 	string 	 
-->

<form action='http://192.168.181.129/oauther/access_token' method='post'>
<div><Input type="hidden" name="client_id" value='123050457758183'></Input></div>
<div><Input type="hidden" name="client_secret" value='secret123'></Input></div>
<div><Input type="hidden" name="grant_type" value='authorization_code'></Input></div>
<div><Input type="hidden" name="redirect_uri" value='http://192.168.181.129/oauther/demo/call_back.php'></Input></div>
<div><Input type="hidden" name="code" value='<?= $_GET['code'] ?>'></Input></div>
<div><Input type="submit"></Input></div>
</form>
