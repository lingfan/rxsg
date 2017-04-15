<?php

	if (!defined("MANAGE_INTERFACE")) exit;

	$ret = sql_fetch_rows("select u.passport,f.name,t.level from sys_technic t left join cfg_technic f on f.tid=t.tid left join sys_user u on u.uid=t.uid where t.state=1 and t.id not in (select id from mem_technic_upgrading)");

?>