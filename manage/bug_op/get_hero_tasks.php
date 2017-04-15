<?php
/**
 * @author 方鸿鹏
 * @method 获取玩家名将专属任务
 * @param $name 君主名  $passport 账号 $starttime 开始时间 $endtime 结束时间
 * @return 
 */

if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($passport)) {
    exit("param_not_exist");
}
if (! isset($name)) {
    exit("param_not_exist");
}
if (! isset($startday)) {
    exit("param_not_exist");
}
if (! isset($endday)) {
    exit("param_not_exist");
}

if(!empty($passport)){
	$uid = sql_fetch_one_cell("select uid from sys_user where passport = '$passport'");
	if(!empty($uid)){
		$ret[] = sql_fetch_rows("select a.hname,a.hid,a.id as tid,a.tname,a.uid,a.uname,a.passport,b.state from 
		(select distinct h.name as hname,h.hid,t.id,t.name as tname,h.uid,u.name as uname,u.passport  
		from sys_city_hero h left join  cfg_task t on h.npcid*10+20001=t.`group`,sys_user u where u.uid=h.uid and t.`id`>400000  and h.uid='$uid') a 
		left join sys_user_task b on a.id=b.tid and a.uid=b.uid order by a.hid,a.id asc");
		$ret[] = sql_fetch_rows("select l.type,u.name as uname,u.passport,h.name as hname,l.count,from_unixtime(time) as time 
		from log_lionize l, sys_user u, sys_city_hero h where l.uid=u.uid and l.npcid=h.npcid and l.uid='$uid' and l.type=0 
		and l.time>=unix_timestamp('$startday') and l.time<unix_timestamp('$endday') order by l.time desc");
		$ret[] = sql_fetch_one_cell("select a.name from sys_city_hero a ,mem_user_schedule b where a.hid=b.last_release_hero and b.uid='$uid'");
	}
	else{
		$ret = 'no data';
	}
}
else{
	if(!empty($name)){
		$uid = sql_fetch_one_cell("select uid from sys_user where name = '$name'");
		
		if(!empty($uid)){
			$ret[] = sql_fetch_rows("select a.hname,a.hid,a.id as tid,a.tname,a.uid,a.uname,a.passport,b.state from 
			(select distinct h.name as hname,h.hid,t.id,t.name as tname,h.uid,u.name as uname,u.passport  
			from sys_city_hero h left join  cfg_task t on h.npcid*10+20001=t.`group`,sys_user u where u.uid=h.uid and t.`id`>400000  and h.uid='$uid') a 
			left join sys_user_task b on a.id=b.tid and a.uid=b.uid order by a.hid,a.id asc");
			$ret[] = sql_fetch_rows("select l.type,u.name as uname,u.passport,h.name as hname,l.count,from_unixtime(time) as time 
			from log_lionize l, sys_user u, sys_city_hero h where l.uid=u.uid and l.npcid=h.npcid and l.uid='$uid' and l.type=0 
			and l.time>=unix_timestamp('$startday') and l.time<unix_timestamp('$endday') order by l.time desc");
			$ret[] = sql_fetch_one_cell("select a.name from sys_city_hero a ,mem_user_schedule b where a.hid=b.last_release_hero and b.uid='$uid'");
		}
		else{
			$ret = 'no data';
		}
	}
	else {
		$ret = 'no data';
	}
}
?>