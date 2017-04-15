<?php
	//每日充值
	//参数列表：
	//startday:开始日期
	//endday:结束日期
	//返回
	//array[0]:array{domainid,cnt,money}
	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($startday)){exit("param_not_exist");}
	if (!isset($endday)){exit("param_not_exist");}
	
	$ret[] = sql_fetch_rows("select u.domainid,count(*) as cnt,sum(p.money) as money from pay_log p,sys_user u where p.passtype=u.passtype and p.passport=u.passport and p.time >= unix_timestamp($startday) and p.time < unix_timestamp($endday)+86400 group by u.domainid;");

?>