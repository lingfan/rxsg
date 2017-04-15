<?php
	//参数列表：
	//cid:cid
	//返回
	//array[]:result
	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($username)){exit("param_not_exist");}
	if (!isset($pwd)){exit("param_not_exist");}
	if (!isset($nick)){exit("param_not_exist");}
	$user = sql_fetch_rows("select * from adm_user where username ='$username'");
	if($user)
	{
		$ret[] = 'failed';
	}
	else{
		$insert = sql_insert(" insert into adm_user (`group`,username,pwd,nick,is_unin) values (7,'$username',md5('$pwd'),'$nick',0);");
		if($insert)
		{
			$ret[] = 'success';
		}
		$ret[] = sql_fetch_rows("select * from adm_user");
	}

?>