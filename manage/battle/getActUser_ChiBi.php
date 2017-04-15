<?php
/**
 * @author 方鸿鹏
 * @method 跨服战场-战场活跃人数查询（赤壁）
 * @param $startday 开始时间 $endday 结束时间
 * @return 
 * Array{
 *   0=>战斗发生次数
 *   1=>占领过野地的人数（每天）
 *   2=>占领过野地的人数（时间段内）
 *   3=>参与过战斗的人数（每天）
 *   4=>参与过战斗的人数（时间段内）
 * }
 */
if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($startday)) {
    exit("param_not_exist");
}
if (! isset($endday)) {
    exit("param_not_exist");
}

$battle_num = sql_fetch_rows("select from_unixtime(time-(time+8*3600)%86400,'%Y-%m-%d') as day, count(*) as count
                              from log_chibi_battle 
                              where time>=unix_timestamp('$startday') 
                              and time<unix_timestamp('$endday')+86400 group by day","chibinet");
$sql_error=mysql_error();

if(!empty($battle_num)&&empty($sql_error)){
	$ret['battle_num'] = $battle_num;
	$attend_user_day = sql_fetch_rows("select from_unixtime(time-(time+8*3600)%86400,'%Y-%m-%d') as day, count(distinct attackuid) as count
									   from log_chibi_battle
									   where time>=unix_timestamp('$startday') 
									   and time<unix_timestamp('$endday')+86400 group by day;","chibinet");

	$sql_error=mysql_error();
	if(!empty($attend_user_day)&&empty($sql_error)){
		$ret['attend_user_day'] = $attend_user_day;
	}
	
	$attend_user_period = sql_fetch_one_cell("select count(distinct attackuid) as count
										   	   from log_chibi_battle 
										   	   where time>=unix_timestamp('$startday') 
										       and time<unix_timestamp('$endday')+86400;","chibinet");
	$sql_error=mysql_error();
	if(!empty($attend_user_period)&&empty($sql_error)){
		$ret['attend_user_period'] = $attend_user_period;
	}
	
	$occupy_user_day = sql_fetch_rows("select from_unixtime(time-(time+8*3600)%86400,'%Y-%m-%d') as day, count(distinct attackuid) as count
										from log_chibi_battle 
										where invade=1 and time>=unix_timestamp('$startday') 
										and time<unix_timestamp('$endday')+86400 group by day;","chibinet");
	$sql_error=mysql_error();
	if(!empty($occupy_user_day)&&empty($sql_error)){
		$ret['occupy_user_day'] = $occupy_user_day;
	}
	
	$occupy_user_period = sql_fetch_one_cell("select count(distinct attackuid) as count
											   from log_chibi_battle 
											   where invade=1 and time>=unix_timestamp('$startday') 
											   and time<unix_timestamp('$endday')+86400;","chibinet");
	$sql_error=mysql_error();
	if(!empty($occupy_user_period)&&empty($sql_error)){
		$ret['occupy_user_period'] = $occupy_user_period;
	}
}
else{
	$ret = 'no data';
}
?>