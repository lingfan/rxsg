<?php
//每日新增充值统计
//参数列表：
//day_start:开始日期
//day_end:结束日期
//返回
//array[0]:array{day,count}
if (!defined("MANAGE_INTERFACE"))
    exit;

if (!isset($day_start)) {
    exit("param_not_exist");
}
if (!isset($day_end)) {
    exit("param_not_exist");
}

$day_list = sql_fetch_rows("select distinct from_unixtime(time,'%Y-%m-%d') as day 
					   from pay_log 
                       where time>=unix_timestamp($day_start) and time<unix_timestamp($day_end)+86400");
$sql_error = mysql_error();
if(!empty($day_list)&&empty($sql_error)){
	$index = 0;
	foreach ($day_list as $every_day){
		$day = $every_day['day'];
		$new_pay_count = sql_fetch_one_cell("select count(distinct passport) as count 
		                                     from pay_log a 
		                                     where time >= unix_timestamp('$day') and time < unix_timestamp('$day')+86400 
		                                     and not exists(
		    											    select 'x' from pay_log b 
		                                                    where b.passtype=a.passtype and b.passport=a.passport 
		                                                    and b.time<unix_timestamp('$day')
		                                              ) 
		                                      group by from_unixtime(time,'%Y-%m-%d')");
		$sql_error = mysql_error();
		if(!empty($new_pay_count)&&empty($sql_error)){
			$ret[$index]['count']=$new_pay_count;
			$ret[$index]['day']=$day;
			$index++;
		}
	}
}
?>