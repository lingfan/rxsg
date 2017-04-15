<?php
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($newonline)) exit('param_not_exist');
	$ret = sql_query("update mem_state set value=$newonline where state=4" );
?>