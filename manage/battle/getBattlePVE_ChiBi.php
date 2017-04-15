<?php
/**
 * @author 方鸿鹏
 * @method 跨服战场-赤壁战场pve数据统计
 * @param $startday 开始时间 $endday 结束时间
 * @return 
 * 		array(
 * 			0=>战场内pve战斗场次
 *          1=>战场内pve战斗消灭的总兵力值（按人口计算）
 *          2=>战场内pve战斗损员的总兵力值（按人口计算）
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
//战场内pve战斗场次
$sql = "select from_unixtime(time-(time+8*3600)%86400,'%Y-%m-%d') as day, count(*) as count 
		from log_chibi_battle 
		where (invade=1 or invade=3) and time>=unix_timestamp('$startday') 
		and time<unix_timestamp('$endday') group by day";
$battle_num = sql_fetch_rows($sql,"chibinet");
$sql_error = mysql_error();
if(!empty($battle_num)&&empty($sql_error)) {
	$ret[] = $battle_num;
}
else{
	$ret[] = 'no data';
}

//战场内pve战斗消灭的总兵力值（按人口计算）
$sql = "select from_unixtime(time-(time+8*3600)%86400,'%Y-%m-%d') as day, sum(resistdie) as num
		from log_chibi_battle 
		where (invade=1 or invade=3) and time>=unix_timestamp('$startday') 
		and time<unix_timestamp('$endday') group by day;";
$resist_num = sql_fetch_rows($sql,"chibinet");
$sql_error = mysql_error();
if(!empty($resist_num)&&empty($sql_error)) {
	$ret[] = $resist_num;
}
else{
	$ret[] = 'no data';
}

//战场内pve战斗损员的总兵力值（按人口计算）
$sql = "select from_unixtime(time-(time+8*3600)%86400,'%Y-%m-%d') as day, sum(attackdie) as num
		from log_chibi_battle 
		where (invade=1 or invade=3) and time>=unix_timestamp('$startday') 
		and time<unix_timestamp('$endday') group by day;";
$attack_num = sql_fetch_rows($sql,"chibinet");
$sql_error = mysql_error();
if(!empty($attack_num)&&empty($sql_error)) {
	$ret[] = $attack_num;
}
else{
	$ret[] = 'no data';
}

?>