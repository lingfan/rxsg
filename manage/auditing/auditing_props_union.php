<?php

	function sendSysMail($touid,$title,$content)
    {
        $title = addslashes($title);
        $content = addslashes($content);

        $mid = sql_insert("insert into sys_mail_sys_content (`content`,`posttime`) values ('$content',unix_timestamp())");
        $sql="insert into sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) values ('$touid','$mid','$title','0',unix_timestamp())";
        sql_insert($sql);
        sql_query("insert into sys_alarm (`uid`,`mail`) values ('$touid',1) on duplicate key update `mail`=1");
    }
	if (!defined("MANAGE_INTERFACE")) exit;
	
	if (!isset($request)){exit("param_not_exist");}
	if (!isset($adm_name)){exit("param_not_exist");}
	
	$name = $request['game_name'];
    $title = $request['mesg_title'];
    $content = $request['mesg_content'];
    $union_users = sql_fetch_rows("select uid from sys_user where union_id='$request[uid]'");
//    $uids = 'uids';
//    foreach($union_users as $u_user)
//    {
//        $uids .= ','.$u_user['uid'];
//    }
//    $uids = str_replace('uids,','',$uids);
	$unin_uid = array();
	foreach ($union_users as $union_user){
		$union_uid[]=$union_user['uid'];
	}
	$uids = implode(',',$union_uid);
    if (empty($request)||($request['state'] != 0))
    {
   		exit("<strong>无效的申请。[<a href=javascript:history.back()>返回</a>]</strong>");
	}
	if($request['gid']=='0')
	{
		sql_query("update sys_user set `gift` = `gift` + $request[count] where `uid` in ($uids)");
		foreach ($union_uid as $uid_one){
			sql_query("insert into log_gift (uid,count,time,type) values ($uid_one,'$request[count]',unix_timestamp(),4)");
		}
		//sql_query("insert into log_gift (uid,count,time,type) (select 'uid','$request[count]',unix_timestamp(),4 from sys_user where uid>1000)");
	}
	elseif($request['gid']=='-100')
	{
		sql_query("update sys_user set `money` = `money` + $request[count] where `uid` in ($uids)");
		foreach ($union_uid as $uid_one){
			sql_query("insert into log_money (uid,count,time,type) values ($uid_one,'$request[count]',unix_timestamp(),4)");
		}
		//sql_query("insert into log_money (uid,count,time,type) (select 'uid','$request[count]',unix_timestamp(),4 from sys_user where uid>1000)");
	}
	else
	{
		sql_insert("insert into sys_goods (`gid`,`uid`,`count`) (select '$request[gid]',uid,'$request[count]' from sys_user where union_id='$request[uid]') ON DUPLICATE KEY UPDATE `count`=`count`+$request[count]");
		foreach ($union_uid as $uid_one){
			sql_query("insert into log_goods (uid,gid,count,time,type) values ($uid_one,'$request[gid]','$request[count]',unix_timestamp(),5)");
		}
		//sql_insert("insert into log_goods (uid,gid,count,time,type) (select uid,'$request[gid]','$request[count]',unix_timestamp(),5 from sys_user where union_id='$request[uid]')");
	}
    
    if(!empty($title) || !empty($content)){
            foreach($union_users as $user){
                sendSysMail($user['uid'],$title,$content);
            }
            if(!empty($request['uid'])){
                //添加log
                $opration_content = '群发送给“'.$name.'”联盟 一封系统信件';
                sql_query("insert into adm_log (`adm_name`,`opration`,`opration_content`,`oprate_time`) values ('$adm_name','send_mesg','$opration_content',unix_timestamp())");
            }
        }
    //添加log
    $opration_content = '审核了给联盟“'.$name.'”'.$request['count'].' 数量的'.$request['name'].'申请';
    $ret[] = sql_query("insert into adm_log (`adm_name`,`opration`,`opration_content`,`oprate_time`) values ('$adm_name','verify_goods_union','$opration_content',unix_timestamp())");
?>