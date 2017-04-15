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

	function sendSysMail($uid,$title,$content)
    {
	    $mid = sql_insert("insert into sys_mail_sys_content (`content`,`posttime`) values ('$content',unix_timestamp())");
        $sql="insert into sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) values ('$uid','$mid','$title','0',unix_timestamp())";
        sql_query($sql);
        sql_query("insert into sys_alarm (`uid`,`mail`) values ('$uid',1) on duplicate key update `mail`=1");
    }
    


	if (!isset($passports)){exit("param_not_exist");}
	if (!isset($names)){exit("param_not_exist");}
	if (!isset($tickets)){exit("param_not_exist");}
	if (!isset($title)){exit("param_not_exist");}
	if (!isset($content)){exit("param_not_exist");}
	$tickets = gzdecode(base64_decode($tickets));
	if ((empty($passports))&&(empty($names)))
	{
		$ret[] = "没有君主名或通行证。";
	}
	else if (empty($tickets))
	{
		$ret[] = "没有礼券码。";
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
				}
			}
		}
		$tickets = explode("\n",$tickets);
		
		if (count($userlist) != count($tickets))
		{
			$ret[] = "有效的帐号数量与礼券数量不符。";
		}
		else
		{
			$cnt = count($userlist);
			for($i =0; $i < $cnt; $i++)
			{
				$ticket = addslashes(trim($tickets[$i]));
				$mycontent = str_replace("%liquan%",$ticket,$content);
				sendSysMail($userlist[$i]['uid'],$title,$mycontent);
				$ret[] = $userlist[$i]['passport']."[".$userlist[$i]['name']."] 发送成功，礼券为\t".$ticket."";
			}
		}
	}

?>