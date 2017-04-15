<?php
/**
 * @author 方鸿鹏
 * @method 跨服战场-道具销售统计查询（赤壁）
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

$sql = "select cs.name,sum(ls.count) as sum, cs.price as price, count(distinct ls.uid) as count 
		from log_shop ls, cfg_shop cs 
		where ls.shopid=cs.gid and ls.shopid>=160001 and ls.shopid<=160051 and ls.paytype=2 
		and ls.time>=unix_timestamp('$startday') and ls.time<unix_timestamp('$endday')+86400 group by shopid;";
$sell_num = sql_fetch_rows($sql,'chibinet');
$sql_error = mysql_error();
if(!empty($sell_num)&&empty($sql_error)){
	$ret[] = $sell_num;
	$sql = "select count(distinct ls.uid) as count 
			from log_shop ls, cfg_shop cs 
			where ls.shopid=cs.gid and ls.shopid>=160001 and ls.shopid<=160051 and ls.paytype=2 
			and ls.time>=unix_timestamp('$startday') and ls.time<unix_timestamp('$endday')+86400;";
	$user_num = sql_fetch_one_cell($sql,'chibinet');
	$sql_error = mysql_error();
	if(!empty($user_num)&&empty($sql_error)){
		$ret[] = $user_num;
	}
}
else{
	$ret='no data';
}
?>