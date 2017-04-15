<?php
	//获得一个城市军队信息
	//参数列表：
	//cid:城市id
	//返回城市科技信息
	if (!defined("MANAGE_INTERFACE")) exit;	
	if (!isset($cid))exit("param_not_exist");
	$result = sql_fetch_rows("select tid, level from sys_city_technic  where cid='$cid' order by tid desc"); 
	$city_technic=array();
	if (empty($result)){
		for ($i=1;$i<21;$i++){			
				$city_technic[$i]=array('tid'=>$i,'level'=>0);			
		}
	}
	else {
		$city_technicno=array();
		
		foreach ($result as $list){
			$key=$list['tid'];
			$city_technicno[]=$list['tid'];
			$city_technic[$key]=$list;
		}
		for ($i=1;$i<21;$i++){
			if (!in_array($i,$city_technicno)){
				$city_technic[$i]=array('tid'=>$i,'level'=>0);
			}
		}
	}
	$ret = $city_technic;
?>