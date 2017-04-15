<?php
	//充值次数
	//参数列表：
	//day_start:开始日期
	//day_end:结束日期
	//返回
	//array[0]:array{day,count}
	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($day_start)){exit("param_not_exist");}
	if (!isset($day_end)){exit("param_not_exist");}
	
	$ret = sql_fetch_rows("select count(passport) as count,from_unixtime(time,'%Y-%m-%d') as day from pay_log where time>=unix_timestamp($day_start) and time< unix_timestamp($day_end)+86400 group by day"); 
?>