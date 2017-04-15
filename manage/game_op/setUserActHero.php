<?php
	if (!defined("MANAGE_INTERFACE")) exit;
	if(!isset($passport)){exit("param_not_exist");}
	if(!isset($heroname)){exit("param_not_exist");}
	$uid = sql_fetch_one_cell("select uid from sys_user where passport='$passport'");
	if(empty($uid)) exit();
	$cid = sql_fetch_one_cell("select cid from sys_city where uid='$uid' limit 1");
	if(empty($cid)) exit();
	$count = sql_fetch_one_cell("select count(1) from sys_city_hero where name='$heroname' and npcid >0");
	if($count >= 2) exit();
	$hid = sql_fetch_one_cell("select hid from sys_city_hero where name='$heroname' and npcid >0");
	if(empty($hid)) exit();
	if(!empty($hid)){
	$oldcid = sql_fetch_one_cell("select cid from sys_city_hero where npcid='$hid'");
	$sql = "update sys_city_hero set uid='$uid',cid='$cid',state=0 where npcid='$hid'";
	sql_query($sql);
	sql_query("update sys_city set chiefhid=0 where chiefhid='$hid' and cid='$oldcid'");
	sql_query("update sys_city set counsellorid=0 where counsellorid='$hid' and cid='$oldcid'");
	sql_query("update sys_city set generalid=0 where generalid='$hid' and cid='$oldcid'");
	sql_query("update sys_user_armor set hid=0 where hid='$hid'");
	sql_query("delete from sys_hero_armor where hid='$hid'");
	sql_query("insert into sys_lionize (uid,npcid,friend,state) values ($uid,$hid,100,2) on duplicate key update state=2");
	sql_query("insert into log_lionize(uid,npcid,`type`,`count`,`time`)  select  uid,npcid,6,friend,unix_timestamp() from sys_lionize where uid='$uid' and npcid='$hid'");
	sql_query("insert into mem_hero_blood (hid,`force_max`,`energy_max`) (select hid,100+ceil(level/5)+ceil((bravery_base+bravery_add)/3)+force_max_add_on,100+ceil(level/5)+ceil(wisdom_base+wisdom_add)/3+energy_max_add_on from sys_city_hero where uid='$uid' ) on duplicate key update `force`=`force`");
	}
	$y = intval($cid/1000);
	$x = $cid-$y*1000;
	$cid = "(".$x.",".$y.")";
	$ret = "已为玩家【".$passport."】在城池".$cid."添加上名将【".$heroname."】";
?>