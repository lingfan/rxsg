<?php

	if (!defined("MANAGE_INTERFACE")) exit;
	
	if (!isset($cid)){exit("param_not_exist");}

	$ret = sql_fetch_rows("select * from sys_city_hero where cid='$cid' order by level desc");  
?>