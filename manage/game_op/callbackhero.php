<?php
	//返回
	//array[]:results of send_sys_mail
	if (!defined("MANAGE_INTERFACE")) exit;
	
	if (!isset($hid)){exit("param_not_exist");}
	$hero = sql_fetch_one("select * from sys_city_hero where hid='$hid' and (state=4 or state=3 or state=2)");
    $troop = sql_fetch_one("select * from sys_troops where hid='$hid'");
    if ((!empty($hero))&&(empty($troop)))
    {
    	sql_query("update sys_city_hero set state=0 where hid='$hid'");
    	$ret['message'] = "成功召回武将[".$hero['name']."]";
    }
    $ret['hero'] = $hero;
    $ret['troop'] = $troop;
?>