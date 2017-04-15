<?php
	//获得城市俘虏营信息
	//参数列表：
	//cid:城市id
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($cid))exit("param_not_exist");
	$ret = sql_fetch_rows("select sc.name as cname,cs.name as sname,mcw.count from mem_city_captive as mcw left join sys_city as sc on mcw.cid=sc.cid left join cfg_soldier as cs on mcw.sid=cs.sid where mcw.cid='$cid'");
	if(empty($ret))$ret = 'no data';
?>