<?php
	//每日充值
	//参数列表：
	//startday:开始日期
	//endday:结束日期
	//返回
	//array[0]:array{orderid,passport,money,time}
	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($startday)){exit("param_not_exist");}
	if (!isset($endday)){exit("param_not_exist");}
	
	$ret[] = sql_fetch_rows("select orderid,passport,money,from_unixtime(time,'%Y-%m-%d') as time from pay_log where time >= unix_timestamp($startday) and time < unix_timestamp($endday)+86400");

?>