<?php

	if (!defined("MANAGE_INTERFACE")) exit;
	
	if (!isset($table_name)){exit("param_not_exist");}
	if (!isset($good_name)){exit("param_not_exist");}
	if (!isset($cell)){exit("param_not_exist");}

	$ret = sql_fetch_one_cell("select `$cell` from $table_name where `name`='$good_name'"); 
?>