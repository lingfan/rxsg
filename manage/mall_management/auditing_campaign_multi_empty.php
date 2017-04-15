<?php
	//删除服务器档期
	//无参数
	//返回1
	if (!defined("MANAGE_INTERFACE")) exit;
	
	sql_query("delete from adm_shop_campaign");
	sql_query("delete from adm_shop_sale");
	$ret = 1;
?>