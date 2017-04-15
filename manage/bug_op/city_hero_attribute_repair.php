<?php
//修复武将空属性
//参数列表：
//uid:用户id
//hid:武将id

//返回
//是否正确执行
if (!defined("MANAGE_INTERFACE")) exit();
if (!isset($uid)) {exit("param_not_exist");}
if (!isset($hid)) {exit("param_not_exist");}

if($hid=='all'){
	sql_query("insert into mem_hero_blood (hid,`force_max`,`energy_max`) 
		(select hid,100+ceil(level/5)+ceil((bravery_base+bravery_add)/3)+force_max_add_on,
		100+ceil(level/5)+ceil(wisdom_base+wisdom_add)/3+energy_max_add_on from sys_city_hero 
		where uid=$uid) on duplicate key update `force`=`force`");
	$ret ['message'] = "全部武将属性修复成功！";
}
else {
	sql_query("insert into mem_hero_blood (hid,`force_max`,`energy_max`) 
		(select hid,100+ceil(level/5)+ceil((bravery_base+bravery_add)/3)+force_max_add_on,
		100+ceil(level/5)+ceil(wisdom_base+wisdom_add)/3+energy_max_add_on from sys_city_hero 
		where uid=$uid and hid = $hid) on duplicate key update `force`=`force`");
	$ret ['message'] = "武将属性修复成功！";
}


?>