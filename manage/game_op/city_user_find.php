<?php
	//修改联盟名
	//参数列表：
	//cid:cid
	//返回
	//array[]:result
	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($user_name)){exit("param_not_exist");}

	$ret = sql_fetch_rows("select * from sys_user where name='$user_name'");
?>