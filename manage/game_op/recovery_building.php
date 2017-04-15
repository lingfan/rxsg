<?php

	if (!defined("MANAGE_INTERFACE")) exit;

	$ret = sql_query("update sys_building set level=1 where level=0 and state=0");

?>