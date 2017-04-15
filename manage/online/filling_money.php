<?php
	//每日充值
	//参数列表：
	//startday:开始日期
	//endday:结束日期
	//返回
	//array[0]:array{day,money}
	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($startday)){exit("param_not_exist");}
	if (!isset($endday)){exit("param_not_exist");}
	
	$rows = sql_fetch_rows("select from_unixtime(time-(time+8*3600)%86400,'%Y-%m-%d') as day,sum(money) as money from pay_log where time >= unix_timestamp($startday) and time < unix_timestamp($endday)+86400 group by (time-(time+8*3600)%86400)");
	$ret[] = $rows;
?>