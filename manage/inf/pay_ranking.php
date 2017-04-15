<?php
/**
 * @author 许孝敦
 * @模块 查询查看 -- 玩家充值排行
 * @功能 
 * @参数
 * @返回 
 * 如果为空 返回 'no data'
 */
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($startday))exit("param_not_exist");
	if (!isset($endday))exit("param_not_exist");
	 //$ret = sql_fetch_rows("select '$server' as server, sum(lm.count) as count,sue.name,sue.passport from log_money as lm 
	 //left join sys_user as sue on sue.uid=lm.uid 
	 //where lm.type=0 and lm.time between unix_timestamp('$startday') and unix_timestamp('$endday')+86400 group by lm.uid order by count desc");	 
	$ret = sql_fetch_rows("select '$server' as server, sum(pl.money) as count,su.name,su.passport from pay_log pl left join sys_user su on pl.passport = su.passport where pl.time between unix_timestamp('$startday') and unix_timestamp('$endday')+86400 group by pl.passport order by count desc");
	if(empty($ret))$ret = 'no data';

?>