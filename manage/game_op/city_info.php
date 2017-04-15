<?php
	//修改联盟名
	//参数列表：
	//cid:cid
	//返回
	//array[]:result
	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($cid)){exit("param_not_exist");}

	$ret = sql_fetch_one("select * from sys_city where cid =$cid ");
?>