<?php
	//参数列表：
	//cid:cid，所操作的城池id
	//uid:uid，分配给用户的id
	//返回
	//string:success
	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($uid)){exit("param_not_exist");}
	if (!isset($cid)){exit("param_not_exist");}
    $old_uid = sql_fetch_one_cell("select uid from sys_city where cid = $cid");
	sql_query("update sys_city set uid = $uid,discardtime = 0  where cid = $cid");
	sql_query("update sys_troops set uid = $uid where cid = $cid and uid = $old_uid");
	sql_query("update sys_city_hero set uid = $uid where cid = $cid and uid = $old_uid");
	sql_query("insert into mem_hero_blood (hid,`force_max`,`energy_max`) (select hid,100+ceil(level/5)+ceil((bravery_base+bravery_add)/3)+force_max_add_on,100+ceil(level/5)+ceil(wisdom_base+wisdom_add)/3+energy_max_add_on from sys_city_hero where uid=$uid and cid = $cid) on duplicate key update `force`=`force`");
	sql_query("update sys_city_res_add set resource_changing=1 where cid= $cid");
	$ret = 'success'.mysql_error();
?>