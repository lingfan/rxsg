<?php
/**
 * @author 方鸿鹏
 * @method 跨服战场-新建城池数量与参与人数
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

$sql = "select from_unixtime(time-(time+8*3600)%86400,'%Y-%m-%d') as day, count(*) as ccount, count(distinct uid) as ucount 
		from sys_city 
		where time>=unix_timestamp('$startday') and time<unix_timestamp('$endday')+86400 group by day";
$city_num = sql_fetch_rows($sql,'chibinet');
$sql_error = mysql_error();
if(!empty($city_num)&&empty($sql_error)){
	$ret[]=$city_num;
	
	$sql = "select count(distinct uid) as ucount 
			from sys_city 
			where time>=unix_timestamp('$startday') and time<unix_timestamp('$endday')+86400";
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