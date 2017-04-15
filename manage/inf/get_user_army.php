<?php
	//获得所有城市军队信息
	//参数列表：
	//uid:用户id
	//返回军队信息
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($uid))exit("param_not_exist");
	$city = sql_fetch_rows("select cid,name from sys_city where uid =$uid ");
	if (!empty($city)){
		$soldiers=array(1,2,3,4,5,6,7,8,9,10,11,12,45,46,47,48,49,50);
		foreach ($city as &$city_list){
			$result = sql_fetch_rows("select c.name as `name`,s.count as `count`,s.sid as `sid` 
			from sys_city_soldier as s,cfg_soldier as c where s.cid='$city_list[cid]' and s.sid=c.sid order by c.sid asc");			
			$army=array();
			$armyno=array();
			foreach ($result as $list){
				$sid=$list['sid'];
				$armyno[]=$list['sid'];
				$army[$sid]=$list;
			}
			foreach ($soldiers as $sid){
				if (!in_array($sid,$armyno)){
					$army[$sid]=array('count'=>0,'sid'=>$sid);
				}
			}
			$city_list['army']=$army;
		}
		$ret=$city;
	}
	else {
		$ret="no data";
	}	
?>