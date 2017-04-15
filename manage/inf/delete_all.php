<?php 
	//删除该用户的所有邮件
	//参数列表：
	//uid
	//返回邮件列表
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($uid))exit("param_not_exist");
	sql_query("delete from sys_mail_content where mid in (select contentid from sys_mail_box where fromuid =$uid)");
    sql_query("delete from sys_mail_box where fromuid = $uid");
    if(mysql_error())
    {
        $ret = 'failed:'.mysql_error();
    }
    else
    {
        $ret = 'delete_succ';
    }
	
?>