<?php
	//用户声望统计
	//参数列表：
	//返回
	//区间
	//人数
	if (!defined("MANAGE_INTERFACE")) exit;
	$ret[0] = sql_fetch_one_cell("select count(*) from sys_user where uid>1000 and prestige=0");
	$ret[1] = sql_fetch_one_cell("select count(*) from sys_user where uid>1000 and prestige<501 and prestige>0");
	$ret[2] = sql_fetch_one_cell("select count(*) from sys_user where uid>1000 and prestige<10001 and prestige>500");
	$ret[3] = sql_fetch_one_cell("select count(*) from sys_user where uid>1000 and prestige<50001 and prestige>10000");
	$ret[4] = sql_fetch_one_cell("select count(*) from sys_user where uid>1000 and prestige<100001 and prestige>50000");
	$ret[5] = sql_fetch_one_cell("select count(*) from sys_user where uid>1000 and prestige<500001 and prestige>100000");
	$ret[6] = sql_fetch_one_cell("select count(*) from sys_user where uid>1000 and prestige<1000001 and prestige>500000");
	$ret[7] = sql_fetch_one_cell("select count(*) from sys_user where uid>1000 and prestige>1000000");
?>