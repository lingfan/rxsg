<?php
	//设置冻结IP
	//参数列表：
	//passports:通行证列表
	//names:君主名列表
	//返回
	//array[]:冻结IP结果
	if (!defined("MANAGE_INTERFACE")) exit;
	function Freeze_ip($uid)
    {
    	$ip = sql_fetch_one_cell("select ip from sys_sessions where uid = $uid");
    	sql_query("insert into cfg_baned_ip (`ip`) values ($ip)");
    	file_put_contents("/bloodwar/server/game/sessions/$uid",0);
    }

	if (!isset($passports)){exit("param_not_exist");}
	if (!isset($names)){exit("param_not_exist");}
	
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
					Freeze_ip($user['uid']);
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
					Freeze_ip($user['uid']);
					$ret[] = $user['passport']."[".$user['name']."] 封禁成功。";
				}
			}
		}
	}
?>