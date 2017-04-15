<?php
	//获得城市建筑信息
	//参数列表：
	//cid:城市id
	//返回城市建筑信息
	if (!defined("MANAGE_INTERFACE")) exit;	
	if (!isset($cid))exit("param_not_exist");
	$city_building = sql_fetch_rows("select s.state_endtime-UNIX_TIMESTAMP() as left_time,s.id as id,s.level as level,s.state as state,c.name as name from sys_building s,cfg_building c where cid='$cid' and s.bid=c.bid order by c.bid ASC");
	if (empty($city_building)){
		$ret = 'no data';
	}
	else {
		$ret = $city_building;
	}

?>