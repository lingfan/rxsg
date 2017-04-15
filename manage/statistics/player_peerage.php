<?php
	//爵位统计
	//参数列表：
	//返回
	//爵位
	//array[0]:用户爵位名称对应id列表
	//array[1]:用户爵位拥有列表
	if (!defined("MANAGE_INTERFACE")) exit;
	
	$ret[0] = sql_fetch_rows("select * from cfg_nobility order by id");
	$ret[1] = sql_fetch_rows("select count(*) as count, n.name from sys_user u, cfg_nobility n where u.nobility = n.id and u.uid>1000 group by u.nobility");
?>