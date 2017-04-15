<?php
//登录奖励统计
//参数列表：
//startday:开始日期
//endday:结束日期
//返回
//array[0]:array{day,money}
if (! defined ( "MANAGE_INTERFACE" ))
	exit ();
if (! isset ( $startday )) {
	exit ( "param_not_exist" );
}
if (! isset ( $endday )) {
	exit ( "param_not_exist" );
}
//$ret= sql_fetch_rows ( "select from_unixtime(gettime,'%Y-%m-%d') as `time`,
//(select count(*) from sys_user_login_reward as tb1 where from_unixtime(tb1.gettime,'%Y-%m-%d')= from_unixtime(tb.gettime,'%Y-%m-%d') and rewardtype ='1') as '1',
//(select count(*) from sys_user_login_reward as tb1 where from_unixtime(tb1.gettime,'%Y-%m-%d')= from_unixtime(tb.gettime,'%Y-%m-%d') and rewardtype ='2') as '2',
//(select count(*) from sys_user_login_reward as tb1 where from_unixtime(tb1.gettime,'%Y-%m-%d')= from_unixtime(tb.gettime,'%Y-%m-%d') and rewardtype ='3') as '3',
//(select count(*) from sys_user_login_reward as tb1 where from_unixtime(tb1.gettime,'%Y-%m-%d')= from_unixtime(tb.gettime,'%Y-%m-%d') and rewardtype ='4') as '4' 
//from sys_user_login_reward as tb 
//where gettime between unix_timestamp('$startday') and unix_timestamp('$endday') + 86400 
//group by from_unixtime(gettime,'%Y-%m-%d')" );
$times = sql_fetch_rows("select distinct(from_unixtime(gettime,'%Y-%m-%d')) as time from sys_user_login_reward where gettime between unix_timestamp('$startday') and unix_timestamp('$endday')+86400");
foreach ($times as $time){
$ret['type'][] = sql_fetch_rows("select count(*) as '1' from sys_user_login_reward where from_unixtime(gettime,'%Y-%m-%d') = '".$time['time']."' and rewardtype ='1'");
$ret['type'][] = sql_fetch_rows("select count(*) as '2' from sys_user_login_reward where from_unixtime(gettime,'%Y-%m-%d') = '".$time['time']."' and rewardtype ='2'");
$ret['type'][] = sql_fetch_rows("select count(*) as '3' from sys_user_login_reward where from_unixtime(gettime,'%Y-%m-%d') = '".$time['time']."' and rewardtype ='3'");
$ret['type'][] = sql_fetch_rows("select count(*) as '4' from sys_user_login_reward where from_unixtime(gettime,'%Y-%m-%d') = '".$time['time']."' and rewardtype ='4'");
}
$ret['time']=$times;
?>