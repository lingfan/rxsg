<?php

	if (!defined("MANAGE_INTERFACE")) exit;
	
	if (!isset($table_name)){exit("param_not_exist");}

	$ret = sql_fetch_rows("select `name` from ".$table_name);
?>