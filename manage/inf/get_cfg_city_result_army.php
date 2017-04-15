<?php
	//获得城市军队信息
	//参数列表：
	//cid:城市id
	//返回军队信息
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($cid))exit("param_not_exist");
	$ret['cfg_army'] = sql_fetch_rows("select * from cfg_soldier");
	$ret['city_army'] = sql_fetch_rows("select c.name as `name`,s.count as `count`,s.cid as `cid`,s.sid as `sid` from sys_city_soldier as s,cfg_soldier as c where cid='$cid' and s.sid=c.sid order by c.sid asc");    
	if(empty($ret))$ret = 'no data';
?>