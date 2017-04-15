<?php
if (!defined("MANAGE_INTERFACE")) exit;

//当前服务器的今日峰值在线人数
$maxOnline = sql_fetch_one("select from_unixtime(time-(time+8*3600)%86400,'%Y-%m-%d') as day,max(online) as online from log_online where time >= unix_timestamp($today) and time < unix_timestamp($today)+86400 group by (time-(time+8*3600)%86400)","bloodwarlog");
//当前服务器的今日充值总额
$money = sql_fetch_one("select from_unixtime(time-(time+8*3600)%86400,'%Y-%m-%d') as day,sum(money) as money from pay_log where time >= unix_timestamp($today) and time < unix_timestamp($today)+86400 group by (time-(time+8*3600)%86400)");
$ret['maxOnline'] = $maxOnline;
$ret['money'] = $money;
?>