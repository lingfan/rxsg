<?php
if (!defined("MANAGE_INTERFACE"))
	exit();
if (!isset($type))
	exit("param_not_exist");
if (!isset($cid))
	exit("param_not_exist");
if ($type == 'sent') {
		sql_query("delete from mem_city_captive where cid = '$cid'");
		$ret=1;
}
elseif ($type == 'recruit') {
	$soldiers = sql_fetch_rows("select * from mem_city_captive where cid='$cid'");
	foreach($soldiers as $soldier)
	{
		$sid=sql_fetch_one_cell("select type from cfg_soldier where sid = ".$soldier['sid']);
		sql_query("insert into sys_city_soldier (cid,sid,count) values ('$cid','$sid','$soldier[count]') on duplicate key update count=count+'$soldier[count]'");
	}
	sql_query("delete from mem_city_captive where cid='$cid'");
	$ret = 1;
}
else {
	$ret = 2;
}
?>