<?php
/**
 * @author 方鸿鹏
 * @method 跨服战场-赤壁战场掉落统计
 * @param $startday 开始时间 $endday 结束时间
 * @return 
 * 		array(
 * 			0=>array(道具名称，掉落数量，获得人数)
 * 		)
 */

if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($startday)) {
    exit("param_not_exist");
}
if (! isset($endday)) {
    exit("param_not_exist");
}

$sql = "select b.name, count(a.count) as goodsNum, count(distinct a.uid) as usersNum  
		from log_goods a, cfg_goods b 
		where a.type=1 and a.gid=b.gid and a.time>=unix_timestamp('$startday') 
		and a.time<unix_timestamp('$endday')+86400 group by a.gid;";
$props_list = sql_fetch_rows($sql,"chibinet");
$sql_error = mysql_error();
if(!empty($props_list)&&empty($sql_error)) {
	$ret[]=$props_list;
	$sql = "select count(distinct a.uid) as count  
			from log_goods a, cfg_goods b 
			where a.type=1 and a.gid=b.gid and a.time>=unix_timestamp('$startday') 
			and a.time<unix_timestamp('$endday')+86400";
	$user_num = sql_fetch_one_cell($sql,"chibinet");
	$sql_error = mysql_error();
	if(!empty($user_num)&&empty($sql_error)) {
		$ret[]=$user_num;
	}
}
else{
	$ret = 'no data';
}
?>