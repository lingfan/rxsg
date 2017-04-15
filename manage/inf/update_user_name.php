<?php
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($name))exit("param_not_exist");
	if (!isset($uid))exit("param_not_exist");
	sql_query("update sys_user set `name`='$name' where uid='$uid'");
?>