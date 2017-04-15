<?php

	if (!defined("MANAGE_INTERFACE")) exit;
	set_time_limit(0);
	function sendSysMail($title,$content)
    {
        $title = addslashes($title);
        $content = addslashes($content);

        $mid = sql_insert("insert into sys_mail_sys_content (`content`,`posttime`) values ('$content',unix_timestamp())");
        $sql="insert into sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) (select uid,'$mid','$title','0',unix_timestamp() from sys_user where uid>1000)";
        sql_insert($sql);
    }
	if (!isset($opration_content)){exit("parama_not_exist");}
	if (!isset($name)){exit("param2_not_exist");}
	if (!isset($content_list)){exit("param3_not_exist");}
	if (!isset($title)){exit("param4_not_exist");}
	if (!isset($mesg_content)){exit("param5_not_exist");}
	$user_list=sql_fetch_rows("select uid from sys_user where uid>1000");
	$uidlist = array();
	foreach($user_list as $user)
	{
		$uidlist[] = $user['uid'];
	}
	$uids = implode(',',$uidlist);
	foreach($content_list as $content)
	{
		if($content[0]==0)
		{
			if($content[1]=='0')
			{
				$number = $content[3];
				sql_query("update sys_user set `gift` = `gift` + $number where `uid` in ($uids)");
				sql_query("insert into log_gift (uid,count,time,type) (select 'uid','$number',unix_timestamp(),4 from sys_user where uid>1000)");
			}
			elseif($content[1]=='-100')
			{
			     $number = $content[3];
				sql_query("update sys_user set `money` = `money` + $number where `uid` in ($uids)");
				sql_query("insert into log_money (uid,count,time,type) (select 'uid','$number',unix_timestamp(),4 from sys_user where uid>1000)");
			}
			else
			{
				$gid = $content[1];
				$number = $content[3];
				sql_insert("insert into sys_goods (`gid`,`uid`,`count`) (select '$gid',uid,'$number' from sys_user where uid>1000) ON DUPLICATE KEY UPDATE `count`=`count`+'$number'");
				sql_insert("insert into log_goods (uid,gid,count,time,type) (select uid,'$gid','$number',unix_timestamp(),5 from sys_user where uid>1000)");
			}
		}else if($content[0]==1)
		{
			$id = $content[1];
			$number = $content[3];
			$_armor = sql_fetch_one("select * from cfg_armor where id = $id");
				for($i=0;$i<$number;$i++)
    			{
					sql_insert("insert into sys_user_armor (uid,armorid,hp,hp_max,hid) (select uid,'$id',$_armor[ori_hp_max]*10,$_armor[ori_hp_max],0 from sys_user where uid>1000)");
				}
    			sql_insert("insert into log_armor (uid,armorid,count,time,type) (select uid,'$id','$number',unix_timestamp(),5 from sys_user where uid>1000)");			
		}else if($content[0]==2)
		{
			$tid = $content[1];
			$number = $content[3];
			sql_insert("insert into sys_things (`tid`,`uid`,`count`) (select '$tid',uid,'$number' from sys_user where uid>1000) ON DUPLICATE KEY UPDATE `count`=`count`+'$number'");
		}
	}
	sendSysMail($title,$mesg_content);
	sql_query("insert into adm_log (`adm_name`,`opration`,`opration_content`,`oprate_time`) values ('$name','verify_compensation','$opration_content',unix_timestamp())");
	$ret = 'success';
?>