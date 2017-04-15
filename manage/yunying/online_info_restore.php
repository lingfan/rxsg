<?php
/**
 * @inform 运营接口 -- 及时数据恢复 --在线人数
 * @author 张昌彪
 * @param null
 * @return 
 * @example  
 */
if (!defined("MANAGE_INTERFACE"))
exit;
try {
	//参数判断
	if (isset($day) && !empty($day)) //如果有某一天的值传递，就返回当天的最高在线
	{
		if($day > time()){
			throw new Exception('date error');
		}
		if (!isset($distance) || empty($distance)) {
			$distance = 24;
		}
		$spacetime = 86400 / $distance;
		for ($i = 0; $i < $distance; $i++) {
			//$result = sql_fetch_one_cell("select count(0) AS `count(*)` from `sys_online` where ($day + $spacetime*($i+1)- `lastupdate`) < 60 ");
			$result = sql_fetch_one_cell("select online from `log_online` where `time` = $day + $spacetime*($i+1)",'bloodwarlog');
			if (empty($result)){$result=0;}
			$cur_online[] = $result;
		}
		if (mysql_error()) {
			throw new Exception(mysql_error());
		}
		$ret['content']['cur_online'] = $cur_online;
	}
}
catch (exception $e) {
	$ret['error'] = $e->getMessage();
}






?>