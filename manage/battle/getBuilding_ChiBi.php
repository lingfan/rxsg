<?php
/**
 * @author 方鸿鹏
 * @method 跨服战场-城池建筑数量与参与人数
 * @param 
 * @return
 * array{
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

$sql = "select from_unixtime(state_starttime-(state_starttime+8*3600)%86400,'%Y-%m-%d') as day, 
		count(*) as ccount, count(distinct uid) as ucount 
		from sys_city c,sys_building b 
		where c.cid=b.cid and b.state_starttime != 0 and state_starttime>=unix_timestamp('$startday') 
		and state_starttime<unix_timestamp('$endday')+86400 group by day";
$building_num = sql_fetch_rows($sql,'chibinet');
$sql_error = mysql_error();
if(!empty($building_num)&&empty($sql_error)){
	$ret[]=$building_num;
	
	$sql = "select count(distinct uid) as ucount 
			from sys_city c,sys_building b 
			where c.cid=b.cid and b.state_starttime != 0 and state_starttime>=unix_timestamp('$startday') 
			and state_starttime<unix_timestamp('$endday')+86400;";
	$user_num = sql_fetch_one_cell($sql,'chibinet');
	$sql_error = mysql_error();
	if(!empty($user_num)&&empty($sql_error)){
		$ret[]=$user_num;
	}
}
else{
	$ret='on data';
}
?>