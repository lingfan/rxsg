<?php
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($newregister)) exit('param_not_exist');
	$ret = sql_query("update mem_state set value='$newregister' where state=100" );
?>