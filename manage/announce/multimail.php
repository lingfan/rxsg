<?php
	//添加玩家邮件
	//参数列表：
	//passports:通行证列表
	//names:君主名列表
	//title:标题
	//content:内容
	//返回
	//array[]:results of send_sys_mail
	if (!defined("MANAGE_INTERFACE")) exit;

	function sendSysMail($admin_name,$uid,$mid,$title)
    {
    	
        $sql="insert into sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) values ('$uid','$mid','$title','0',unix_timestamp())";
        sql_query($sql);
        sql_query("insert into sys_alarm (`uid`,`mail`) values ('$uid',1) on duplicate key update `mail`=1");
        sql_query("insert into adm_log (`adm_name`,`opration`,`opration_content`,`oprate_time`) values ('$admin_name','send_mesg','群发多个玩家邮件',unix_timestamp())");
    }                              
    


	if (!isset($passports)){exit("param_not_exist");}
	if (!isset($names)){exit("param_not_exist");}
	if (!isset($admin_name)){exit('param_not_exist');}
	if (!isset($title)){exit("param_not_exist");}
	if (!isset($content)){exit("param_not_exist");}

	if ((empty($passports))&&(empty($names)))
	{
		$ret[] = "没有君名主或通行证";
	}
	else
	{
	    $mid = sql_insert("insert into sys_mail_sys_content (`content`,`posttime`) values ('$content',unix_timestamp())");
	    
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
					sendSysMail($admin_name,$user['uid'],$mid,$title,$content);
					$ret[] = $user['passport']."[".$user['name']."] 发送成功。";
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
					sendSysMail($admin_name,$user['uid'],$mid,$title,$content);
					$ret[] = $user['passport']."[".$user['name']."] 发送成功。";
				}
			}
		}
	}

?>