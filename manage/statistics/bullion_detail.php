<?php
	//每日充值详单
	//参数列表：
	//startday:开始日期
	//endday:结束日期
	//返回
	//array[0]:array{type,passport,name，money,time}
	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($startday)){exit("param_not_exist");}
	if (!isset($endday)){exit("param_not_exist");}
	
	$rows = sql_fetch_rows("select l.type,u.passport,u.name,u.uid,l.count as money,from_unixtime(l.time,'%Y-%m-%d') as time,l.time as longtime from log_money l,sys_user u where l.time >= unix_timestamp($startday) and l.time < unix_timestamp($endday)+86400 and l.count<0 and l.uid = u.uid");
	$shoplist = sql_fetch_rows("select * from cfg_shop");
	$shoptype = array();
	foreach($shoplist as $type)
	{
		$shoptype[$type['id']] = $type['name'];
	}
	foreach($rows as &$row)
	{
		if($row['type']==10)
		{
			$shop = sql_fetch_one("select * from log_shop where time = '$row[longtime]' and uid = '$row[uid]'");
			$row['shop'] = '商城购买'.$shoptype[$shop['shopid']].$shop['count'].'个';
		}
	}
	$ret = $rows;

?>