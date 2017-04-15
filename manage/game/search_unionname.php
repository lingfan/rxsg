<?php
	//查询城池
	//参数列表：
	//name:城池名
	//返回
	//array[0]:user_info
	if (!defined("MANAGE_INTERFACE")) exit;
	$name = addslashes($name);
	$ret[] = sql_fetch_rows("select n.*,u.name as leadername from sys_union n left join sys_user u on u.uid=n.leader where n.name like '%".$name."%'");
?>