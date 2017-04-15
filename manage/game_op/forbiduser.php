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
	function ForbidUser($uid,$delemail,$forbidStart,$forbidEnd)
    {
    	
		sql_query("update mem_city_resource set forbidden=1 where cid in (select cid from sys_city where uid='$uid')");
		sql_query("insert into sys_user_state (uid,forbistart,forbiend) values ('$uid',unix_timestamp('$forbidStart'),unix_timestamp('$forbidEnd')) on duplicate key update forbistart=unix_timestamp('$forbidStart'),forbiend=unix_timestamp('$forbidEnd')");
		file_put_contents("/bloodwar/server/game/sessions/$uid",0);
		if ($delemail){
			sql_query("delete from sys_mail_box where fromuid='$uid'");
		}
    }

	if (!isset($passports)){exit("param_not_exist");}
	if (!isset($names)){exit("param_not_exist");}
	if (!isset($delemail)){exit("param_not_exist");}
	if (!isset($forbidStart)){exit("param_not_exist");}
	if (!isset($forbidEnd)){exit("param_not_exist");}
	
	$fail_list = array();
	$success_list = array();
	if (!empty($passports))
	{
		foreach($passports as $passport)
		{
			$user = sql_fetch_one("select uid, name, passport from sys_user where uid > 1000 and passport='$passport' limit 1");

			if (empty($user))
			{
				$fail_list[] = "不存在帐号：".$passport."。";
			}
			else
			{
				ForbidUser($user['uid'],$delemail,$forbidStart,$forbidEnd);
				$success_list[] = $user;
			}
		}
	}
	else
	{
		foreach($names as $name)
		{
			$user = sql_fetch_one("select uid, name, passport from sys_user where uid > 1000 and name='$name' limit 1");
			if (empty($user))
			{
				$fail_list[] = "不存在君主名：".$name;
			}
			else
			{
				ForbidUser($user['uid'],$delemail,$forbidStart,$forbidEnd);
				$success_list[] = $user;
			}
		}
	}
	if(!empty($fail_list))
		$ret['fail'] = $fail_list;
	if(!empty($success_list))
		$ret['success'] = $success_list;
?>