<?php

	if (!defined("MANAGE_INTERFACE")) exit;

	$ret = sql_query("insert into mem_technic_upgrading (select id,cid,tid,`level`+1,state_endtime from sys_technic where state=1 and id not in (select id from mem_technic_upgrading))");

?>