<?php
	//每日充值
	//参数列表：
	//startday:开始日期
	//endday:结束日期
	//返回
	//array[0]:array{orderid,passport,money,time}
	if (!defined("MANAGE_INTERFACE")) exit;
$ret = array();
	$ret[] = sql_fetch_one_cell("select count(0) AS `count(*)` from `sys_online` where ((unix_timestamp() - `lastupdate`) < 60) "); 
	$ret[] = sql_fetch_one_cell("select count(*) from sys_city");
	$ret[] = sql_fetch_one_cell("select count(*) from sys_user where uid > 1000");
	$ret[] = sql_fetch_one_cell("select value from mem_state where state=2");

?>