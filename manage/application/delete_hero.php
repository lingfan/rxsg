<?php
	if (!defined("MANAGE_INTERFACE")) exit;
	
	if (!isset($hid)){exit("param_not_exist");}
	
	$hero = sql_fetch_one("select * from sys_city_hero where hid = $hid");
	if($hero['state'] != 0)
	{
		$ret[] = 'failed';
		$ret[] = '状态非空闲不能删除';
	}
	else
	{
		$ret[] = 'success';
		sql_query("delete from sys_city_hero where hid=$hid and state=0");
	}
?>