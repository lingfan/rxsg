<?php
/**
 * @author 张昌彪
 * @模块 运营数据 -- 日付费比例统计
 * @功能 获得当前服务器日付费比例统计
 */
if (! defined ( "MANAGE_INTERFACE" ))
	exit ();
if (! isset ( $startday )) {
	exit ( "param_not_exist" );
}
if (! isset ( $endday )) {
	exit ( "param_not_exist" );
}
//当日付费人数
//$pay_count = sql_fetch_one_cell("select count(passport) from (select passport from pay_log where time <unix_timestamp('$day')+86400 and time > unix_timestamp('$day') group by passport) p");
//当日上线人数
//$login_count = sql_fetch_rows("select count(distinct(uid)) as lcount,from_unixtime(time,'%Y-%m-%d') as ptime from log_login where time between unix_timestamp('2009-06-29')and unix_timestamp('2009-06-29')+86400 group by from_unixtime(time,'%Y-%m-%d')");
/*$ret = sql_fetch_rows ( "select count(distinct(pl.passport)) /count(distinct(lg.uid)) *100 as count,from_unixtime(pl.time,'%Y-%m-%d') as time from pay_log as pl
left join  log_login as lg on from_unixtime(pl.time,'%Y-%m-%d') =from_unixtime(lg.time,'%Y-%m-%d')  
where pl.time between unix_timestamp('$startday') and unix_timestamp('$endday')+86400 
group by from_unixtime(pl.time,'%Y-%m-%d')");"*/
//$ret = sql_fetch_rows ( "select count(distinct(pl.passport)) /count(distinct(lg.uid)) *100 as count,from_unixtime(pl.time,'%Y-%m-%d') as time from pay_log as pl,log_login as lg where pl.time between unix_timestamp('$startday') and unix_timestamp('$endday')+86400 and from_unixtime(pl.time,'%Y-%m-%d') =from_unixtime(lg.time,'%Y-%m-%d')  group by from_unixtime(pl.time,'%Y-%m-%d')");
//$ret = round(100*$pay_count/$login_count,4);



$times = sql_fetch_rows("select distinct(from_unixtime(time,'%Y-%m-%d')) as time from pay_log where time between unix_timestamp('$startday') and unix_timestamp('$endday')+86400");
foreach ($times as $time){
	$day = $time['time'];
	$pay_count = sql_fetch_one_cell("select count(passport) from (select passport from pay_log where time <unix_timestamp('$day')+86400 and time > unix_timestamp('$day') group by passport) p");
	$login_count = sql_fetch_one_cell("select count(distinct uid) as lcount from log_login where time>=unix_timestamp('$day') and time<unix_timestamp('$day')+86400;");
	$ret[$day] = round(100*$pay_count/$login_count,4);
}
?>