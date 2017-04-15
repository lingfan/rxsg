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

	if (!isset($passport)){exit("param_not_exist");}
	if (!isset($name)){exit("param_not_exist");}
	
	if ((empty($passport))&&(empty($name)))
	{
		$ret['message'] = "没有君主名或通行证";
	}
	else if(!empty($name))
	{
    	$touid = sql_fetch_one_cell("select `uid` from sys_user where `name`='$name' limit 1");            
    }
    else if(!empty($passport))
    {
    	$touid = sql_fetch_one_cell("select `uid` from sys_user where `passport`='$passport' limit 1");      
    }
    if(empty($touid))
    {
     	exit("<strong>没有相关信息[<a href=javascript:history.back()>返回</a>]</strong>");
    }
    $user = sql_fetch_one("select * from sys_user where uid='$touid'"); 
    $ret['touid'] = $touid;
    $ret['user'] = $user;
?>