<?php
/**
 * @author 许孝敦
 * @模块 查询查看 -- 查询联盟
 * @功能 通过联盟id查询联盟充值信息
 * @参数 $id 联盟id
 * @返回 
 * 如果为空 返回 'no data'
 */
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($id))exit("param_not_exist");
	$ret = sql_fetch_rows("select su.name as unname,sue.name,lm.count,from_unixtime(lm.time) as time from log_money as lm 
	left join sys_user as sue on sue.uid = lm.uid and lm.type=0 
	left join sys_union as su on su.id=sue.union_id 
	where union_id='$id' and lm.`time` between unix_timestamp('$startday') and unix_timestamp('$endday')+86400
	");
	if(empty($ret))$ret = 'no data';

?>