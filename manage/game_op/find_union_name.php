<?php
	//查询联盟名称
	//参数列表：
	//union_id:联盟id
	//返回
	//string:union_name
	if (!defined("MANAGE_INTERFACE")) exit;
	if(!isset($union_id))exit('no params exit');
	$ret = sql_fetch_one_cell("select name from sys_union where id = $union_id");
?>