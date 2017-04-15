<?php
	//获得联盟名
	//参数列表：
	//union_id:联盟id
	//返回联盟名
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($union_id))exit("param_not_exist");
	$ret=sql_fetch_one_cell("select name from sys_union where id='$union_id'");
if(empty($ret))$ret = 'no data';
?>