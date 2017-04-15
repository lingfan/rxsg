<?php
	
	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($passports)){exit("param_not_exist");}
	if (!isset($names)){exit("param_not_exist");}
	if (!isset($prop_list)){exit("param_not_exist");}
	
	if ((empty($passports))&&(empty($names)))
	{
		$ret[] = "没有君主名或通行证。";
	}
	else
	{
		$userlist = array();
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
					$userlist[] = $user;
					$touid[] = $user['uid'];
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
					$userlist[] = $user;
					$touid[] = $user['uid'];
				}
			}
		}

    	$ret['touid'] = $touid;
    	$ret['user'] = $userlist;
    }
?>