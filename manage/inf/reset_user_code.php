<?php
    if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($uid))exit("param_not_exist");
	sql_query("delete from sys_user_password where uid=$uid");
	$ret[]=1;
?>