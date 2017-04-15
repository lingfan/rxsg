<?php
	//每日元宝消耗
	//参数列表：
	//startday:开始日期
	//endday:结束日期
	//返回
	//array[0]:array{day,money}
	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($startday)){exit("param_not_exist");}
	if (!isset($endday)){exit("param_not_exist");}
	$ret = sql_fetch_rows("select count(uid) as count, sum(money) as money,day from (select sum(count) as money,uid,day from (select uid, from_unixtime(time,'%Y-%m-%d') as day, count  from log_money where time >= unix_timestamp($startday) and time < unix_timestamp($endday)+86400 and count<0 order by day,uid) as q group by q.day,q.uid) as p group by day");

?>