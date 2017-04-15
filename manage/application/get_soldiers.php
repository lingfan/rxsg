<?php

	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($cid)){exit("param_not_exist");}

	$ret['cfg_army'] = sql_fetch_rows("select * from cfg_soldier where fromcity=1");
	$ret['city_army'] = sql_fetch_rows("select c.name as `name`,s.count as `count`,s.cid as `cid`,s.sid as `sid` from sys_city_soldier as s,cfg_soldier as c where cid='$cid' and s.sid=c.sid order by c.sid asc"); 
 	$food=sql_fetch_one("select food,food_add from mem_city_resource where cid='$cid'");
	$ret['food']=$food;
	
?>