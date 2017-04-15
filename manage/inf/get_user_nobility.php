<?php
	
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($nobility))exit("param_not_exist");

	$ret = sql_fetch_one_cell("select name from cfg_nobility where id='$nobility'");
	if(empty($ret))$ret = 'no data';

?>