<?php
require_once("./common.php");

$starttime=$_POST['starttime'];
$endtime=$_POST['endtime'];

if (empty($starttime) || empty($endtime)) exit("time must not be empty"); 

//$result = sql_fetch_rows("select a.passport,b.count,from_unixtime(b.time) as time from sys_user a,log_month_money b where a.uid=b.uid and b.time between $starttime and $endtime");
$result = sql_fetch_rows("select substring(from_unixtime(time),1,7) as month,sum(count) as money from log_month_money where time between $starttime and $endtime group by month");

$str=json_encode($result);
exit("$str");