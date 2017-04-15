<?php
//修复市井传闻查到武将在某个野地，占领该野地却发现武将不存在的问题
//参数列表：hid 武将id
//返回
//是否正确执行
if (!defined("MANAGE_INTERFACE")) exit();
if (!isset($hid)) exit("param_not_exist");
	
sql_query("update sys_city_hero set uid=0,state=4 where hid=$hid");
$ret['message'] = "武将修复成功！";

?>