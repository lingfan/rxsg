<?php
	//获得城市武将信息
	//参数列表：
	//cid:城市id
	//返回武将信息
	if (!defined("MANAGE_INTERFACE")) exit;	
	if (!isset($cid))exit("param_not_exist");
	$city_heros = sql_fetch_rows("select * from sys_city_hero where cid='$cid' order by level desc");
	if (empty($city_heros)){
		$ret = 'no data';
	}
	else {
		$ret = $city_heros;
	}
?>