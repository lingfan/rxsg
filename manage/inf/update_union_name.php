<?php
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($name))exit("param_not_exist");
	if (!isset($id))exit("param_not_exist");
	$ret = sql_query("update sys_union set `name`='$name' where id='$id'");
if(empty($ret))$ret = 'no data';
?>