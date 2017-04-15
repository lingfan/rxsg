<?php
	//设置登录公告
	//参数列表：
	//passports:通行证列表
	//names:君主名列表
	//title:标题
	//content:内容
	//返回
	//array[]:results of send_sys_mail
	if (!defined("MANAGE_INTERFACE")) exit;

	$ret[] = sql_fetch_rows("select u.*,from_unixtime(s.forbistart) as forbistart,from_unixtime(s.forbiend) as forbiend from sys_user_state s left join sys_user u on u.uid=s.uid where s.forbiend = 2000000000");
?>