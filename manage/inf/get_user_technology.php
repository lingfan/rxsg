<?php
	//许孝敦
	//获得一个科技信息
	//参数列表：
	//Uid:用户id
	//返回科技信息
	if (!defined("MANAGE_INTERFACE")) exit;	
	if (!isset($uid))exit("param_not_exist");
	$ret = sql_fetch_rows("select *,ct.name, ct.description,sc.name as scname,CONCAT(sc.cid%1000,',',floor(sc.cid/1000)) as position from sys_technic t left join cfg_technic ct on ct.tid = t.tid left join sys_city as sc on t.cid=sc.cid where t.uid='$uid'");
?>