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
	function ForbidUser($uid,$delemail)
    {
    	
		sql_query("update mem_city_resource set forbidden=1 where cid in (select cid from sys_city where uid='$uid')");
		sql_query("insert into sys_user_state (uid,forbistart,forbiend) values ('$uid',unix_timestamp(),2000000000) on duplicate key update forbistart=unix_timestamp(),forbiend=2000000000");
		file_put_contents("../game/sessions/$uid",0);
		if ($delemail){
			sql_query("delete from sys_mail_box where fromuid='$uid'");
		}
    }

	if (!isset($passports)){exit("param_not_exist");}
	if (!isset($names)){exit("param_not_exist");}
	if (!isset($delemail)){exit("param_not_exist");}
	
	if ((empty($passports))&&(empty($names)))
	{
		$ret[] = "没有君主名或通行证";
	}
	else
	{
		
		if (!empty($passports))
		{
			$passports = explode("\n",$passports);
			foreach($passports as $passport)
			{
				$passport=addslashes(trim($passport));
				$user = sql_fetch_one("select * from sys_user where uid > 1000 and passport='$passport' limit 1");

				if (empty($user))
				{
					$ret[] = "不存在帐号：".$passport."。";
				}
				else
				{
					ForbidUser($user['uid'],$delemail);
					$ret[] = $user['passport']."[".$user['name']."] 封禁成功。";
				}
			}
		}
		else
		{
			$names = explode("\n",$names);
			foreach($names as $name)
			{
				$name=addslashes(trim($name));
				$user = sql_fetch_one("select * from sys_user where uid > 1000 and name='$name' limit 1");
				if (empty($user))
				{
					$ret[] = "不存在君主名：".$name;
				}
				else
				{
					ForbidUser($user['uid'],$delemail);
					$ret[] = $user['passport']."[".$user['name']."] 封禁成功。";
				}
			}
		}
	}
?>