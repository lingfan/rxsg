<?php
	//模块 公告管理 -- 全服管理
 	//功能 添加全服邮件
	//
	//参数列表：
	//title:标题
	//content:内容
	//正确执行完成返回 1
	
	if (!isset($title)){exit("param_not_exist");}
	if (!isset($content)){exit("param_not_exist");}
	if (!isset($admin_name)){exit("param_not_exist");}
	
	$mid = sql_insert("insert into sys_mail_sys_content (`content`,`posttime`) values ('$content',unix_timestamp())");
	$sql="insert into sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) select uid,$mid,'$title','0',unix_timestamp() from sys_user where uid > 895"; 
	sql_query($sql);             
	sql_query("insert into sys_alarm (`uid`,`mail`) (select uid,1 from sys_user where uid > 895) on duplicate key update `mail`=1");
	sql_query("insert into adm_log (`adm_name`,`opration`,`opration_content`,`oprate_time`) values ('$admin_name','send_mesg','群发所有玩家邮件',unix_timestamp())");
    $ret=1;
?>