<?php
//列表城市武将101等级
//参数列表：无
//返回
//array[]:

if (!defined("MANAGE_INTERFACE")) exit;

$ret = sql_fetch_rows("select h.hid as hid, h.level as level,h.uid as uid,h.name as name,u.passport as passport,u.name as user_name 
		from sys_city_hero h, sys_user u where h.uid = u.uid and h.level = '101'");


?>