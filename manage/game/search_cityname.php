<?php
	//查询城池
	//参数列表：
	//name:城池名
	//返回
	//array[0]:user_info
	if (!defined("MANAGE_INTERFACE")) exit;
	$name = addslashes($name);
	$ret[] = sql_fetch_rows("select c.cid,concat(c.cid%1000,',',floor(c.cid/1000)) as position,c.name,u.name as username from sys_city c left join sys_user u on u.uid=c.uid where c.name like '%".$name."%'");
?>