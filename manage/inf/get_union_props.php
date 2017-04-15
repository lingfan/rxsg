<?php
/**
 * @author 许孝敦
 * @模块 查询查看 -- 查询联盟
 * @功能 通过联盟id查询联盟道具消耗信息
 * @参数 $id 联盟id
 * @返回 
 * 如果为空 返回 'no data'
 */
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($id))exit("param_not_exist");
	$ret = sql_fetch_rows("select so.name,su.name as so_name,cg.name as cg_name,from_unixtime(lg.`time`) as `time`,sum(lg.`count`) as `count` from sys_union as so 
	left join sys_user as su on su.union_id = so.id 
	left join log_goods as lg on su.uid  = lg.uid 
	left join cfg_goods as cg on cg.gid=lg.gid 
	where so.id='$id' and lg.type = '0' and lg.`time` between unix_timestamp('$startday') and unix_timestamp('$endday')+86400 
	group by cg.name order by so.name");
	if(empty($ret))$ret = 'no data';

?>