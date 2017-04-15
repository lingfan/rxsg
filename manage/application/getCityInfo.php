<?php

	if (!defined("MANAGE_INTERFACE")) exit;
	
	if (!isset($cid)){exit("param_not_exist");}

	$ret = sql_fetch_one("select `uid`,`name` from sys_city where cid='$cid'");
 
?>