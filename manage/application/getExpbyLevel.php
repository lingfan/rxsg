<?php

	if (!defined("MANAGE_INTERFACE")) exit;
	
	if (!isset($level)){exit("param_not_exist");}

	$ret = sql_fetch_one_cell("select `total_exp` from cfg_hero_level where level='$level'"); 
?>