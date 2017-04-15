<?php
	//获得铜币使用信息
	//参数列表：
	//cid:城市id
	//许孝敦
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($uid))exit("param_not_exist");
	$ret = sql_fetch_rows("select sl.*,sch.name,su.name as uname,su.passport from sys_lionize sl left join sys_city_hero sch on sch.npcid=sl.npcid left join sys_user su on sl.uid=su.uid where sl.uid='$uid'");
	if(empty($ret))$ret = 'no data';
?>