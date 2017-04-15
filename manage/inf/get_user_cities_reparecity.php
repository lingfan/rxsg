<?php
	//城市修复
	//参数列表：
	//cid:城市id
	//返回ok
	if (!defined("MANAGE_INTERFACE")) exit;	
	if (!isset($cid))exit("param_not_exist");
	
	
	//民房 N级增长人口上限100*N
	$people_max = sql_fetch_one_cell("select sum(level*(level+1)*50)+100 from sys_building where `cid`='$cid' and bid=5");
	sql_query("update mem_city_resource set `people_max`='$people_max' where `cid`='$cid'");
	
	
	//人口稳定值=人口上限*民心
	$people_stable = sql_fetch_one_cell("select floor(`people_max`*`morale`*0.01) from mem_city_resource where `cid`='".$cid."'");
	sql_query("update mem_city_resource set `people_stable`='$people_stable' where `cid`='$cid'");
	
	$gold_max = sql_fetch_one_cell("select sum(level*(level+1)*500000) from sys_building where `cid`='$cid' and bid=6");
	sql_query("update mem_city_resource set `gold_max`='$gold_max' where `cid`='$cid'");
	
	$people_building = sql_fetch_one_cell("select sum(l.using_people) from sys_building b left join cfg_building_level l on b.bid=l.bid and b.level=l.level where b.cid='$cid' and  b.xy >= 100");
	sql_query("update mem_city_resource set `people_building`='$people_building' where `cid`='$cid'");
	sql_query("update sys_city_res_add set resource_changing=1 where cid='$cid'");
	$ret="ok";

?>