<?php
//查找需要修改所属权的城市
//参数列表：
//city_name:city_name
//返回
//array[]:result
if (!defined("MANAGE_INTERFACE"))
	exit();

if (!isset($city_name)) {
	exit("param_not_exist");
}
if (!isset($type)) {
	exit("param_not_exist");
}
if ($type == 'precise') {
	$city_list = sql_fetch_rows("select CONCAT(cid%1000,',',floor(cid/1000)) as position,cid,uid,name,discardtime from sys_city where `uid`>=895 and name='$city_name'");
	foreach ($city_list as &$city) {
		$city['user_name'] = sql_fetch_one_cell("select name from sys_user where `uid` = " . $city['uid']);
		if ($city['uid'] == 895 && $city['discardtime'] != 0) {
			$level = sql_fetch_one_cell("select level from sys_building where cid = $city[cid] and type = 6");
			$temp_time = $city['discardtime'] - 86400 * $level;
			$city['checktime'] = sql_fetch_one_cell("select from_unixtime($temp_time)");
		}
	}
}
else 
	if ($type == 'fuzzy') {
		$city_list = sql_fetch_rows("select CONCAT(cid%1000,',',floor(cid/1000)) as position,cid,uid,name,discardtime from sys_city where  `uid`>=895 and name like '%$city_name%'");
		foreach ($city_list as &$city) {
			$city['user_name'] = sql_fetch_one_cell("select name from sys_user where `uid` = " . $city['uid']);
			if ($city['uid'] == 895 && $city['discardtime'] != 0) {
				$level = sql_fetch_one_cell("select level from sys_building where cid = $city[cid] and type = 6");
				$temp_time = $city['discardtime'] - 86400 * $level;
				$city['checktime'] = sql_fetch_one_cell("select from_unixtime($temp_time)");
			}
		}
	}
$ret = $city_list;
?>