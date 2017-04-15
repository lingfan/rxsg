<?php
	//每日元宝囤积统计（多服）
	//参数列表：
	//$startday
	//$endday
	//返回
	//array[0]:array{day,count}
	if (!defined("MANAGE_INTERFACE")) exit;

	foreach($days as $day)
	{
		$money[] = '(select sum(count) from log_money where time<' . strtotime($day) . '+86400) \'' . $day . '\'';
		$number[] = '(select count(uid) from (select uid from log_money where time<' . strtotime($day) . '+86400 group by uid having sum(count)>0) a) \'' . $day . '\'';
	}

	$moneysql = "select " . implode(',', $money);
	$numbersql = "select " . implode(',', $number);

	$ret['money'] = sql_fetch_one($moneysql);
	$ret['number'] = sql_fetch_one($numbersql);

	
//	$ret = sql_fetch_one("select count(distinct uid) number,sum(`count`) money
//											from log_money
//											where time>=unix_timestamp('$startday') and time<unix_timestamp('$endday')+86400"); 
?>