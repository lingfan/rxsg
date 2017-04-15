<?php
	//每日联盟充值
	//参数列表：
	//startday:开始日期
	//endday:结束日期
	//返回
	//array[0]:array{day,money}
	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($startday)){exit("param_not_exist");}
	if (!isset($endday)){exit("param_not_exist");}
	

	$ret = sql_fetch_rows("select from_unixtime(lm.time,'%Y-%m-%d') as day,sum(lm.count) as count
											from sys_union as su,sys_user as sue,log_money as lm 
											where su.id=sue.union_id and lm.uid=sue.uid and lm.type=0 and lm.time >= unix_timestamp('$startday') and lm.time < unix_timestamp('$endday')+86400 
											group by from_unixtime(lm.time,'%Y-%m-%d')");
	
?>