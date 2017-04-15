<?php
	//查看玩家发送信件列表
	//参数列表：
	//passport or name
	//返回邮件列表
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($name))exit("param_not_exist");
	if (!isset($passport))exit("param_not_exist");
    if (!isset($search_type))exit("param_not_exist");
    if(!empty($name))
    {
        if($search_type=='accuracy')
        {
            $userlist = sql_fetch_rows("select * from sys_user where name = '$name'");
        }
        else
        {
            $userlist = sql_fetch_rows("select * from sys_user where name like '%$name%'");
        }
    }
    elseif(!empty($passport))
    {
        if($search_type=='accuracy')
        {
            $userlist = sql_fetch_rows("select * from sys_user where passport='$passport'");
        }
        else
        {
            $userlist = sql_fetch_rows("select * from sys_user where passport like '%$passport%'");
        }
    }
    if(empty($userlist))
    {
        $ret = array();
    }
    else
    {
        $email_list = array();
        foreach($userlist as $user)
        {
            $list = sql_fetch_rows("select b.fromuid,b.mid,b.name,b.fromname,b.title,c.content,from_unixtime(b.posttime) as time from sys_mail_box b,sys_mail_content c where b.fromuid='$user[uid]' and b.contentid = c.mid");
            $email_list = array_merge_recursive($email_list,$list);
        }
        $ret = $email_list;
    }
    
	
?>