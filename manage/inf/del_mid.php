<?php
	//查看玩家发送信件列表
	//参数列表：
	//passport or name
	//返回邮件列表
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($mids))exit("param_not_exist");
    sql_query("delete from sys_mail_content where mid in (select contentid from sys_mail_box where mid in ($mids))");
    sql_query("delete from sys_mail_box where mid in ($mids)");
	if(mysql_error())
	{
	   $ret = 'del_failed: '.mysql_error().$mids;
	}
	else
	{
	   $ret = 'del_success';
	}
?>