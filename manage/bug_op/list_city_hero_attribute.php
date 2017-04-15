<?php
//列表城市武将及属性，等级
//参数列表：
//passports:通行证
//names:君主名

//返回
//array[]:
if (! defined ( "MANAGE_INTERFACE" ))exit ();
function List_city_hero($uid) {
	return sql_fetch_rows ( "select  a.hid as hid, a.uid as uid, a.name as name, a.level as level,a.name as city_name, 
		a.passport as passport, a.name as user_name, mem_hero_blood.force as `force`,mem_hero_blood.force_max as force_max,
		mem_hero_blood.energy as energy,mem_hero_blood.energy_max as energy_max  
		from (select h.hid as hid, h.uid as uid, h.name as name, h.level as level,c.name as city_name, 
		u.passport as passport, u.name as user_name from sys_city_hero h, sys_city c, sys_user u 
		where h.cid =c.cid and h.uid = u.uid and h.uid = '$uid' order by h.cid) as a left join mem_hero_blood  
		on a.hid = mem_hero_blood.hid" );
}
if (! isset ( $passport )) {	exit ( "param_not_exist" );}
if (! isset ( $name )) {	exit ( "param_not_exist" );}

if ((empty ( $passport )) && (empty ( $name ))) {
	$ret ['message'] = "没有君主名或通行证";
} 
else if (! empty ( $passport )) {
	$passport = addslashes ( trim ( $passport ) );
	$user = sql_fetch_one ( "select * from sys_user where uid > 1000 and passport='$passport' limit 1" );	
	if (empty ( $user )) {
		$ret ['message'] = "不存在帐号：" . $passport . "。";
	} else {
		$ret = List_city_hero ( $user ['uid'] );	
	}
} else {
	$name = addslashes ( trim ( $name ) );
	$user = sql_fetch_one ( "select * from sys_user where uid > 1000 and name='$name' limit 1" );
	if (empty ( $user )) {
		$ret ['message'] = "不存在君主名：" . $name;
	} else {
		$ret = List_city_hero ( $user ['uid'] );
	}
}

?>