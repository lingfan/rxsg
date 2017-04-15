<?php
//修复武将等级
//参数列表：无
//返回
//是否正确执行
if (!defined("MANAGE_INTERFACE")) exit();

	sql_query("update sys_city_hero set level=100 where level=101");
	$ret['message'] = "全部武将等级修复成功！";

?>