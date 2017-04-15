<?php
/**
 * @author 方鸿鹏
 * @method 跨服战场-道具消耗统计查询（赤壁）
 * @param $startday 开始时间 $endday 结束时间
 * @return 
 */

if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($startday)) {
    exit("param_not_exist");
}
if (! isset($endday)) {
    exit("param_not_exist");
}

$sql = "select cg.name, -sum(lg.count) as sum, count(distinct lg.uid) as count 
		from cfg_goods cg, log_goods lg 
		where cg.gid=lg.gid and lg.gid>=160001 and lg.gid<=160051 and type=0 
		and time>=unix_timestamp('$startday') and time<unix_timestamp('$endday')+86400 group by lg.gid";
$consume_num = sql_fetch_rows($sql,'chibinet');
$sql_error = mysql_error();
if(!empty($consume_num)&&empty($sql_error)){
	$ret[]=$consume_num;
	$sql = "select count(distinct lg.uid) as count 
			from cfg_goods cg, log_goods lg 
			where cg.gid=lg.gid and lg.gid>=160001 and lg.gid<=160051 and type=0 
			and time>=unix_timestamp('$startday') and time<unix_timestamp('$endday')+86400";
	$user_num = sql_fetch_one_cell($sql,'chibinet');
	$sql_error = mysql_error();
	if(!empty($user_num)&&empty($sql_error)){
		$ret[]=$user_num;
	}
}
else{
	$ret='no data';
}
?>