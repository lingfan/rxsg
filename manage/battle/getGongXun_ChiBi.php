<?php
/**
 * @author 方鸿鹏
 * @method 跨服战场-功勋值统计查询（赤壁）
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

$sql = "select from_unixtime(time-(time+8*3600)%86400,'%Y-%m-%d') as day, 
		(count(distinct resistuid)+count(distinct attackuid)) as count, sum(attackgongxun+resistgongxun) as gongxun 
		from log_chibi_battle 
		where (attackgongxun!=0 or resistgongxun != 0) and time>=unix_timestamp('$startday') 
		and time<unix_timestamp('$endday')+86400 group by day;";
$gongxun_value = sql_fetch_rows($sql,'chibinet');
$sql_error = mysql_error();
if(!empty($gongxun_value)&&empty($sql_error)){
	$ret[]=$gongxun_value;
	//查询attack uid
	$sql = "select distinct attackuid 
			from log_chibi_battle 
			where attackgongxun!=0 and time>=unix_timestamp('$startday') 
			and time<unix_timestamp('$endday')+86400";
	$attack_num = sql_fetch_rows($sql,'chibinet');
	$sql_error = mysql_error();
	if(!empty($attack_num)&&empty($sql_error)){
		$ret[]=$attack_num;
	}
	//查询resist uid
	$sql = "select distinct resistuid
			from log_chibi_battle 
			where resistgongxun != 0 and time>=unix_timestamp('$startday') 
			and time<unix_timestamp('$endday')+86400";
	$resist_num = sql_fetch_rows($sql,'chibinet');
	$sql_error = mysql_error();
	if(!empty($resist_num)&&empty($sql_error)){
		$ret[]=$resist_num;
	}
}
else{
	$ret='no data';
}
?>