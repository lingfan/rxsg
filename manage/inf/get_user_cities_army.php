<?php
	//获得一个城市军队信息
	//参数列表：
	//cid:城市id
	//返回军队信息
	if (!defined("MANAGE_INTERFACE")) exit;	
	if (!isset($cid))exit("param_not_exist");
	$soldiers=array(1,2,3,4,5,6,7,8,9,10,11,12,45,46,47,48,49,50);
	$result = sql_fetch_rows("select c.name as `name`,s.count as `count`,s.sid as `sid` 
	from sys_city_soldier as s,cfg_soldier as c where s.cid='$cid' and s.sid=c.sid order by c.sid asc");
	
	$army=array();
	$armyno=array();
	if(is_array($result)){
		foreach ($result as $list){
			$sid=$list['sid'];
			$armyno[]=$list['sid'];
			$army[$sid]=$list;
		}
	}	
	foreach ($soldiers as $sid){
		if (!in_array($sid,$armyno)){
			$army[$sid]=array('count'=>0,'sid'=>$sid);
		}
	}
	$ret=$army;
?>