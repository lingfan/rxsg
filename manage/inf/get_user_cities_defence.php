<?php
//获取城市的城防
//参数cid
//返回城防建筑数
if (!defined("MANAGE_INTERFACE"))
	exit();
if (!isset($cid))
	exit("param_not_exist");
$ret = sql_fetch_rows("select cfg_defence.name,sys_city_defence.count from sys_city_defence,cfg_defence where sys_city_defence.did = cfg_defence.did and sys_city_defence.cid ='$cid'");
if (empty($ret))
	$ret = 'no data';

?>