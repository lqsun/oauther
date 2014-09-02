<form action='<?= $authPage?>' method='post'>
<div><span>User:</span><Input type="text" name="userName"></Input></div>
<div><span>Password:</span><Input type="password" name="userPassword"></Input></div>
<div><Input type="submit"></Input></div>
<div><Input type="hidden" name="getArray" value='<?= $getArray?>'></Input></div>
</form>
<?php
	foreach($privilege as $k => $v){
		if($v){echo "<div>$k</div>";}
	}
?>
