<?php
	//名城占有率
	//参数列表：
	//返回
	//总数
	//占有数
	if (!defined("MANAGE_INTERFACE")) exit;
	
	$ret[0] = sql_fetch_rows("select count(*) as count, p.name from sys_city c,cfg_province p where c.type>0 and p.id = c.province group by c.province");
	$ret[1] = sql_fetch_rows("select count(*) as count, p.name from sys_city c,cfg_province p where c.type>0 and c.uid>1000 and p.id = c.province group by c.province");
?>