<?php
/**
 * @author 许孝敦
 * @模块 查询查看 -- 查询联盟登陆用户
 * @功能 
 * @参数 
 * @返回 
 * 如果为空 返回 'no data'
 */
	if (!defined("MANAGE_INTERFACE")) exit;
	//if (!isset($id))exit("param_not_exist");
	$ret = sql_fetch_rows("select '$server' as server,su.name,sue.name as sname,from_unixtime(lg.time) as time from sys_union as su left join sys_user as sue on su.id = sue.union_id left join log_login as lg on sue.uid=lg.uid and lg.time between unix_timestamp('$startday') and unix_timestamp('$endday')+86400");
	if(empty($ret))$ret = 'no data';

?>