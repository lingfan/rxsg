<?php
	//踢玩家下线
	//参数列表：
	//passports:通行证列表
	//names:君主名列表

	//返回
	//array[]:处理成功与否
	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($passports)){exit("param_not_exist");}
	if (!isset($names)){exit("param_not_exist");}
	$ret = '';
	if ((empty($passports))&&(empty($names)))
	{
		$ret = "没有君主名或通行证";
	}
	else
	{
		
		if (!empty($passports))
		{
			$passports = explode("\n",$passports);
			foreach($passports as $passport)
			{
				//$passport=addslashes(trim($passport));
				$user = sql_fetch_one("select * from sys_user where uid > 1000 and passport='$passport' limit 1");

				if (empty($user))
				{
					$ret .= "不存在帐号：".$passport."。</br>";
				}
				else
				{
					file_put_contents("/bloodwar/server/game/sessions/$user[uid]",0);
					$ret .= $user['passport']."[".$user['name']."] 成功踢下线。</br>";
				}
			}
		}
		else
		{
			$names = explode(" ",$names);
			foreach($names as $name)
			{
				//$name=addslashes(trim($name));
				$user = sql_fetch_one("select * from sys_user where uid > 1000 and name='$name' limit 1");
				if (empty($user))
				{
					$ret .= "不存在君主名：".$name."</br>";
				}
				else
				{
					file_put_contents("/bloodwar/server/game/sessions/$user[uid]",0);
					$ret .= $user['passport']."[".$user['name']."] 成功踢下线。</br>";
				}
			}
		}
	}
?>