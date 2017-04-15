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
    if (empty($request)||($request['state'] != 0))
    {
   		exit("<strong>无效的申请。[<a href=javascript:history.back()>返回</a>]</strong>");
	}
    $union_users = sql_fetch_rows("select uid from sys_user where union_id='$request[uid]'");
    $_armor = sql_fetch_one("select * from cfg_armor where id = ".$request['aid']);
    foreach($union_users as $user){
    	
    	for($i=0;$i<$request['count'];$i++)
	    {
	    	sql_query("insert into sys_user_armor (uid,armorid,hp,hp_max,hid) values ('$user[uid]','$request[aid]',$_armor[ori_hp_max]*10,$_armor[ori_hp_max],0)");
	    }
	    sql_query("insert into log_armor (uid,armorid,count,time,type) values ('$user[uid]','$request[aid]','$request[count]',unix_timestamp(),5) ON DUPLICATE KEY UPDATE `count`=`count`+'$request[count]'");
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