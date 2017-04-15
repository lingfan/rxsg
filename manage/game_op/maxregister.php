<?php
	if (!defined("MANAGE_INTERFACE")) exit;
	$ret = sql_fetch_one_cell("select value from mem_state where state=100");
?>