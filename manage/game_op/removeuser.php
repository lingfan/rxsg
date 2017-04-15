<?php

	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($uid)){exit("param_not_exist");}
	
	sql_query("update sys_user set union_id=0 where uid = $uid");
	$ret = "操作成功";
?>