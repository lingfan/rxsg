<?php 
require_once './utils.php';

//选取所有的玩家uid
$users = sql_fetch_rows("select distinct uid from sys_troops where task=1 and state in (0,4) and uid>1000");

foreach ($users as $user) {
	$uid = $user['uid'];
	//选取一个人所有的城池
	$citys = sql_fetch_one_cell("select group_concat(cid) from sys_city where uid=$uid");
	$wilds = sql_fetch_rows("select wid from mem_world where ownercid in (select cid from sys_city where uid=$uid)");
	
	//选取所有的军队
	$troops = sql_fetch_rows("select id,uid,cid,targetcid from sys_troops where task=1 and state in (0,4) and uid=$uid");
	$troopIds ="0";
	foreach ($troops as $troop) {
		//找出军队驻扎的野地编号
		$targetWid = cid2wid($troop['targetcid']);
		if (!in_array($targetWid,$wilds)) {
			$troopIds .= ','.$troop['id'];
		}
	}
	//不再自己野地的军队，都召回
	sql_query("update sys_troops set state=1 where id in ({$troopIds})");
	
	//增加7天休假和7天招安
	if (sql_check("select 1 from sys_user_state where uid=$uid")) {
		sql_query("update sys_user_state set vacstart=unix_timestamp()-2*86400,vacend=unix_timestamp()+7*86400 where uid=$uid");
	} else {
		sql_query("insert into sys_user_state(uid,vacstart,vacend) values($uid,unix_timestamp()-2*86400,unix_timestamp()+7*86400)");
	}
	sql_query("update mem_city_resource set vacation=1 where cid in ($citys)");
	
	sql_query("insert into mem_user_buffer(uid,buftype,endtime) values($uid,10333,unix_timestamp()+7*86400) on duplicate key update endtime=endtime+7*86400");
}
echo "ok \n";