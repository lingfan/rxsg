<?php
/**
 * @author 许孝敦
 * @模块 查询查看 -- 联盟充值查询
 * @功能 查询各服务器联盟充值信息
 * @参数
 * @返回 
 * 如果为空 返回 'no data'
 */
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($startday))exit("param_not_exist");
	if (!isset($endday))exit("param_not_exist");
	/*$ret = sql_fetch_rows("select su.name,sum(lm.count) from log_money as lm 
	left join sys_user as sue on lm.uid = sue.uid 
	left join sys_union as su on su.id=sue.union_id 
	where lm.type = 0 group by su.name");*/
	 $ret = sql_fetch_rows("select '$server' as server,su.name,sum(lm.count) as count,sue.name as sname from sys_union as su
	 left join sys_user as sue on su.id=sue.union_id 
	 left join log_money as lm on lm.uid=sue.uid and lm.type=0 and lm.time between unix_timestamp('$startday') and unix_timestamp('$endday')+86400 
	 group by su.name");
	if(empty($ret))$ret = 'no data';

?>