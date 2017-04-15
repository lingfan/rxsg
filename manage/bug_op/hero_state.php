<?php
//参数列表：无
//返回
//array[]:result
if (! defined("MANAGE_INTERFACE"))
    exit();
    if (!isset($passport)) {exit("param_not_exist");}
    if (!isset($heroname)) {exit("param_not_exist");}
	$uid = sql_fetch_one_cell("select uid from sys_user where passport='$passport'");
	$hero = sql_fetch_one("select hid,state from sys_city_hero where name = '$heroname' and uid='$uid'");
	$troops = sql_fetch_one("select task,state from sys_troops where uid='$uid' and hid='".$hero['hid']."'");
	$state = $hero['state'];
	$arr_state = array(0,1,5,6,7,8,9);
	if(!empty($troops) && in_array($state,$arr_state)){
		sql_query("update sys_troops set task=1,state=1,endtime=unix_timestamp() where uid='$uid' and hid='".$hero['hid']."' limit 1");
		sql_query("update sys_city_hero set state=0 where uid='$uid' and hid='".$hero['hid']."' limit 1");
	$ret = "修复成功";
	}else{
		$ret = "正常，不需要修复";
	}
?>