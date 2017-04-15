<?php

	if (!defined("MANAGE_INTERFACE")) exit;	
	if (!isset($uid)){exit("param_not_exist");}
	
	$ret['user'] = sql_fetch_one("select * from sys_user where uid = '$uid' limit 1");
	if(!empty($ret['user']))
	{
		
		$ret['uid'] = $uid;
		$ret['city'] = sql_fetch_rows("select c.cid as cid,c.name as name,c.type as type,c.state as state,people,gold,morale,complaint,food,food_add,wood,rock,iron,CONCAT(c.cid%1000,',',floor(c.cid/1000)) as position from sys_city c,mem_city_resource r where c.uid='$uid' and c.cid=r.cid");
	}
	return $ret;
?>