<?php

	if (!defined("MANAGE_INTERFACE")) exit;
	
	if (!isset($name)){exit("param_not_exist");}

	$ret = sql_fetch_rows("select `name` from cfg_goods where name like '%$name%'"); 
?>