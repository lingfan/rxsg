<?php
	//设置登录公告
	//参数列表：
	//msg:公告内容
	//返回
	//true:update_succ
	//failed:update_failed
	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($msg)){exit("param_not_exist");}

	sql_query("update sys_announce set content='$msg' where id=1");
	if(mysql_error())
	{
	   $ret[] = "update_failed";	
	}
	else
	{
	   $ret[] = "update_succ";
    }	

?>