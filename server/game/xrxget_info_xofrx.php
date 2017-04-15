<?php
require_once("./dbinc.php");
if(empty($_GET['time']) || empty($_GET['key']))
{
	exit('param_invalid');
}
if(getStrIP()!='122.225.28.5'){
	exit('ip_invalid');
}
$key_master="Rp#Cu##alIETXAQsUmUEjCCCl>K1Bpm>FP8QZ6]MvD";
$remotetime = $_GET['time'];
$key = $_GET['key'];
$delay=time()-$remotetime;
if(!($delay>-3600&&$delay<3600))
{
	exit("time_out");
}
$retstr="";
if(strtoupper($key) == strtoupper(md5($remotetime.$key_master)))
{
	$today=sql_fetch_one_cell("select unix_timestamp()-(unix_timestamp()+8*3600)%86400");
	$online=intval(sql_fetch_one_cell("select count(*) from sys_online where lastupdate > unix_timestamp()-30"));
	$online600=intval(sql_fetch_one_cell("select count(*) from sys_online where lastupdate > unix_timestamp()-600"));
	$reg=intval(sql_fetch_one_cell("select count(*)-895 from sys_user"));
	//$regmax=intval(sql_fetch_one_cell("select value from mem_state where state=100"));
	//$cities=intval(sql_fetch_one_cell("select count(*) from sys_city"));
	$dau=intval(sql_fetch_one_cell("select count(*) from sys_online where lastupdate>=$today"));
	$pay_user=intval(sql_fetch_one_cell("select count(distinct(passport)) from pay_log where time>=$today and time<$today+86400"));
	$pay_user2=intval(sql_fetch_one_cell("select count(distinct(passport)) from pay_log where time>=$today-86400 and time<$today"));
	$money=intval(sql_fetch_one_cell("select money from pay_day_money where day=$today"));
	$money2=intval(sql_fetch_one_cell("select money from pay_day_money where day=$today-86400"));
	$submoney=intval($money-$money2);
	$total=intval(sql_fetch_one_cell("select sum(money) from pay_day_money"));
	//$arpdau=$money/($dau==0?1:$dau);
	$retstr= sprintf("%d,%d,%d,%d,%d,%d,%d,%d,%d,%d",$online,$online600,$reg,$dau,$pay_user,$pay_user2,$money,$money2,$submoney,$total);
}
$retstr=trim($retstr);
echo $retstr;

function getStrIP()
{
	$ip="0.0.0.0";
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])&&!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
	{
		$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	elseif (isset($_SERVER['REMOTE_ADDR'])&&!empty($_SERVER['REMOTE_ADDR']))
	{
		$ip=$_SERVER['REMOTE_ADDR'];	
	}
	elseif (isset($_SERVER['HTTP_CLIENT_IP'])&&!empty($_SERVER['HTTP_CLIENT_IP']))
	{
		$ip=$_SERVER['HTTP_CLIENT_IP'];
	}
	return ($ip);	
}