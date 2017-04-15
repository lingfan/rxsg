<?php
	//获得城市科技信息
	//参数列表：
	//cid:城市id
	//返回城市名。君主名，科技信息
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($cid))exit("param_not_exist");
	$ret['city_technic'] = sql_fetch_rows("select s.level as level,c.name as name from sys_city_technic s,cfg_technic c where s.cid='$cid' and s.tid=c.tid order by s.tid desc");
	$ret['city_name'] = sql_fetch_one("select `name`,uid from sys_city where cid='$cid'");
	$ret['username'] = sql_fetch_one_cell("select `name` from sys_user where uid=".$ret['city_name']['uid']);
	if(empty($ret))$ret = 'no data';
?>