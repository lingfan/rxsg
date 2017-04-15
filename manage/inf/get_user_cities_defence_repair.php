<?php
//根据城防空间，平分给各个城防
//参数cid
//返回城防建筑数
if (!defined("MANAGE_INTERFACE"))
	exit();
if (!isset($cid))
	exit("param_not_exist");
	$area=sql_fetch_one_cell("select area from sys_city_area where cid='$cid'");
	if($area>0){
		$defences=sql_fetch_rows("select * from cfg_defence order by did");
		$total_area_need=0;
		$area_need=array();
		foreach ($defences as $df){
			$did=$df['did'];
			$area_need[$did]=$df['area_need'];
		}
		$city_defence=sql_fetch_rows("select * from sys_city_defence where cid='$cid' order by did");
		$df_count=count($city_defence);
		if($df_count>0){
			$total_count=0;
			foreach($city_defence as $df){
				$did=$df['did'];
				$total_count+=$df['count']*$area_need[$did];
			}
			if($total_count<$area){
				foreach($city_defence as $df){
					$did=$df['did'];
					$count=floor($area/$df_count/$area_need[$did]);
					sql_query("replace into sys_city_defence (cid,did,`count`) values ('$cid','$did','$count')");
				}
			}
		}
	}
	$ret = sql_fetch_rows("select cfg_defence.name,sys_city_defence.count from sys_city_defence,cfg_defence where sys_city_defence.did = cfg_defence.did and sys_city_defence.cid ='$cid'");
	if (empty($ret)){
		$ret = 'no data';
	}
?>