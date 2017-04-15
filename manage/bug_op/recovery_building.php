<?php

	if (!defined("MANAGE_INTERFACE")) exit;

	//$ret = sql_query("update sys_building set level=1 where level=0 and state=0");
	sql_query("update sys_building set level=level+1,state=0 where state=1 and id not in (select id from mem_building_upgrading) and state_starttime<unix_timestamp()-60");
	sql_query("update sys_building set level=level-1,state=0 where state=2 and id not in (select id from mem_building_destroying) and state_starttime<unix_timestamp()-60");
	$ret['message'] = "修复成功！";
	?>