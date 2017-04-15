<?php
	require_once("./dbinc.php");
	if(empty($_GET['time']) || empty($_GET['key']))
	{
        exit('');
    }
    $key_local_gm="3v2)-+VWL>Q*3!0V.kdY2";
    $key_local="_2*lPqld{qv_27_3.vq$-s3*";
    $remotetime = $_GET['time'];
    $key = $_GET['key'];
    $delay=time()-$remotetime;
    if(!($delay>-600&&$delay<600))
    {
    	exit("");
    }
    if(strtoupper($key) == strtoupper(md5($remotetime.$key_local)))
    {
    	$online=intval(sql_fetch_one_cell("select count(*) from sys_online where lastupdate > unix_timestamp()-30"));
		$reg=intval(sql_fetch_one_cell("select count(*)-895 from sys_user"));
		$regmax=intval(sql_fetch_one_cell("select value from mem_state where state=100"));
		$cities=intval(sql_fetch_one_cell("select count(*) from sys_city"));
		echo sprintf("%d,%d,%d,%d",$online,$reg,$regmax,$cities);
    }
    else if(strtoupper($key) == strtoupper(md5($remotetime.$key_local_gm)))
    {
    	$online=intval(sql_fetch_one_cell("select count(*) from sys_online where lastupdate > unix_timestamp()-30"));
		$reg=intval(sql_fetch_one_cell("select count(*)-895 from sys_user"));
		$regmax=intval(sql_fetch_one_cell("select value from mem_state where state=100"));
		$cities=intval(sql_fetch_one_cell("select count(*) from sys_city"));
		$money=intval(sql_fetch_one_cell("select floor(money/10) from pay_day_money where day=unix_timestamp()-(unix_timestamp()+8*3600)%86400"));
		$money2=intval(sql_fetch_one_cell("select floor(money/10) from pay_day_money where day=unix_timestamp()-(unix_timestamp()+8*3600)%86400-86400"));
		$submoney=intval($money-$money2);
		echo sprintf("%d,%d,%d,%d,%d,%d,%d",$online,$reg,$regmax,$cities,$money,$money2,$submoney);
    }
?>