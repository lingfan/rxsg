<?php
	//元宝充值
	//参数列表：
	//money:金额
	//time:时间范围
	//返回
	//array{date,count,times,total,rate}
	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($money)){exit("param_not_exist");}
	if (!isset($startday)){exit("param_not_exist");}
	if (!isset($endday)){exit("param_not_exist");}
	$total = sql_fetch_one_cell("select sum(count) from log_money where type = 0 and time >= unix_timestamp($startday) and time < unix_timestamp($endday)+86400");
	$row = sql_fetch_one("select count(*) as times, sum(count) as count,sum(money) as money,from_unixtime(unix_timestamp($startday),'%y-%m-%d') as startday, from_unixtime(unix_timestamp($endday),'%y-%m-%d') as endday from (select count(count) as count,sum(count) as money,uid from log_money where type = 0 and count>=$money and time >= unix_timestamp($startday) and time < unix_timestamp($endday)+86400 group by uid) as p");
	$ret['date'] = $row['startday'].' 到 '.$row['endday'];
	$ret['count'] = $row['count'];
	$ret['times'] = $row['times'];
	$ret['rate'] = empty($total)?0:$row['money']/$total;
	$ret['total'] = $total;
?>