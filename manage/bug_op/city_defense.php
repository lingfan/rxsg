<?php
 //城防建筑卡0秒
	if (!defined("MANAGE_INTERFACE")) exit;
	sql_query("replace into mem_city_reinforce (id,cid,did,count,state_endtime) (select id,cid,did,count,state_starttime+needtime from sys_city_reinforcequeue where state=1 and state_starttime<unix_timestamp()-60)");
	$ret['message'] = "修复成功！";
?>