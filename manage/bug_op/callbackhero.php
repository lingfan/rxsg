<?php
//返回
//array[]:results of send_sys_mail
if (!defined("MANAGE_INTERFACE"))
	exit();

if (!isset($hid)) {
	exit("param_not_exist");
}
$hero = sql_fetch_one("select * from sys_city_hero where hid='$hid' and (state=4 or state=3 or state=2 or state=10 or state=11)");
$troop = sql_fetch_one("select * from sys_troops where hid='$hid' and uid>1000");
if ((!empty($hero)) && (empty($troop))) {
	sql_query("update sys_city_hero set state=0 where hid='$hid'");
	if ($hero['state'] == 10 || $hero['state'] == 11) {
		$money = sql_fetch_one_cell("SELECT carrymoney FROM `sys_hero_expr` where hid='$hid'");
		if (!empty($money)) {
			sql_query("update sys_user set money=money+$money where uid='$hero[uid]'");
			sql_query("delete FROM `sys_hero_expr` where hid=$hid");
			$ret['message'] = "成功召回武将[" . $hero['name'] . "]并返还元宝" . $money;
		}
		else {
			sql_query("delete FROM `sys_hero_expr` where hid=$hid");
			$ret['message'] = "成功召回武将[" . $hero['name'] . "]，无元宝返还";
		}
	}
	else
		$ret['message'] = "成功召回武将[" . $hero['name'] . "]";
}
//    $ret['hero'] = $hero;
//    $ret['troop'] = $troop;
?>