<?php
	require_once("./interface.php");
	require_once("./utils.php");
	/*
	function getUserBuffer($uid,$param)
	{
		$ret=array();
		$nowtime=sql_fetch_one_cell("select unix_timestamp()");
		$bufs=sql_fetch_rows("select * from mem_user_buffer where uid='$uid' and endtime>'$nowtime'");
		$ret[]=$nowtime;
		$ret[]=$bufs;
		return $ret;
	}
	
	function getCityBuffer($uid,$param)
	{
		$cid=array_shift($param);
		$userid=sql_fetch_one_cell("select uid from sys_city where cid='$cid'");
		if($uid!=$userid)
		{
			throw new Exception($GLOBALS['getCityBuffer']['not_your_city']);
		}
		$ret=array();
		$nowtime=sql_fetch_one_cell("select unix_timestamp()");
		$bufs=sql_fetch_rows("select * from mem_city_buffer where cid='$cid' and endtime>'$nowtime'");
		$ret[]=$nowtime;
		$ret[]=$bufs;
		return $ret;
	}
	*/
	function  getChiBiCraftBuffer($uid){
		$chibicraft=sendChibiRemoteRequest($uid,"getCraftBuffer");
		array_shift($chibicraft);
		$chibicraft=array_shift($chibicraft);
		return $chibicraft;
	}
	
	function getCraftBuffer($uid,$param)
	{
		$ret=array();
		$nowtime=sql_fetch_one_cell("select unix_timestamp()");
		$bufs=sql_fetch_rows("select * from mem_user_buffer where uid='$uid' and buftype not in(8,23,10321,130527) and endtime>'$nowtime'");			
		$unionbuf=getUnionBuffer($uid);
		for($i=0;$i<sizeof($bufs);$i++){
			if($bufs[$i]['buftype']==161501){				
				array_splice($bufs,$i,1);
			}
		}
		if(sizeof($unionbuf)>0){
			$bufs=array_merge($bufs,$unionbuf);
		}
		$ret[]=$nowtime;
		$ret[]=$bufs;
		return $ret;
	}
	function getUnionBuffer($uid){
		$unionid=sql_fetch_one_cell("select `union_id` from sys_user where uid=$uid");
		$creator=sql_fetch_one_cell("select `leader` from sys_union where id=$unionid");
		$nowtime=sql_fetch_one_cell("select unix_timestamp()");
		$bufs=sql_fetch_rows("select * from mem_user_buffer where uid='$creator' and endtime>'$nowtime' and buftype=161501");
		return $bufs;
	}
	
	function getTrickBuffer($uid,$param)
	{
		$cid=intval(array_shift($param));
		$userid=sql_fetch_one_cell("select uid from sys_city where cid='$cid'");
		if($uid!=$userid)
		{
			throw new Exception($GLOBALS['getCityBuffer']['not_your_city']);
		}
		$ret=array();
		$nowtime=sql_fetch_one_cell("select unix_timestamp()");
		$bufs=sql_fetch_rows("select * from mem_city_buffer where cid='$cid' and endtime>'$nowtime'");
		$ret[]=$nowtime;
		$ret[]=$bufs;
		return $ret;
	}
	
	function getYaoYiLinTime($uid,$param)
	{
		$ret=array();
		$ret[]=intval(sql_fetch_one_cell("select endtime from mem_user_buffer where uid='$uid' and buftype=11 and endtime>unix_timestamp()"));
		return $ret;
	}
?>
