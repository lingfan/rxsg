<?php

function sendSysMail($touid, $title, $content) {
	$title = addslashes ( $title );
	$content = addslashes ( $content );
	
	$mid = sql_insert ( "insert into sys_mail_sys_content (`content`,`posttime`) values ('$content',unix_timestamp())" );
	$sql = "insert into sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) values ('$touid','$mid','$title','0',unix_timestamp())";
	sql_insert ( $sql );
	sql_query ( "insert into sys_alarm (`uid`,`mail`) values ('$touid',1) on duplicate key update `mail`=1" );
}
if (! defined ( "MANAGE_INTERFACE" ))
	exit ();

if (! isset ( $request )) {
	exit ( "param_not_exist" );
}
if (! isset ( $adm_name )) {
	exit ( "param_not_exist" );
}

$name = $request ['game_name'];
$title = $request ['mesg_title'];
$content = $request ['mesg_content'];
if (empty ( $request ) || ($request ['state'] != 0)) {
	exit ( "<strong>无效的申请。[<a href=javascript:history.back()>返回</a>]</strong>" );
}
if ($request ['gid'] == '0') {
	$count = sql_fetch_one_cell ( "select gift from sys_user where uid='$request[uid]'" );
	if (abs ( $request [count] ) > $count && $request [count] < 0) {
		$request [count] = '-' . $count;
	}
	sql_query ( "update sys_user set gift=gift+$request[count] where uid='$request[uid]'" );
	sql_query ( "insert into log_gift (uid,count,time,type) values ('$request[uid]','$request[count]',unix_timestamp(),4)" );
} elseif ($request ['gid'] == '-100') {
	$count = sql_fetch_one_cell ( "select money from sys_user where uid='$request[uid]'" );
	if (abs ( $request [count] ) > $count && $request [count] < 0) {
		$request [count] = '-' . $count;
	}
	sql_query ( "update sys_user set money=money+$request[count] where uid='$request[uid]'" );
	sql_query ( "insert into log_money (uid,count,time,type) values ('$request[uid]','$request[count]',unix_timestamp(),4)" );
} else {
	$count = sql_fetch_one_cell ( "select count from sys_goods where gid='$request[gid]' and uid='$request[uid]'" );
	if (abs ( $request [count] ) > $count && $request [count] < 0) {
		$request [count] = '-' . $count;
	}
	sql_query ( "insert into sys_goods (`gid`,`uid`,`count`) values ('$request[gid]','$request[uid]','$request[count]') ON DUPLICATE KEY UPDATE `count`=`count`+'$request[count]'" );
	sql_query ( "insert into log_goods (uid,gid,count,time,type) values ('$request[uid]','$request[gid]','$request[count]',unix_timestamp(),5)" );
}

if (! empty ( $title ) || ! empty ( $content )) {
	if (! empty ( $request ['uid'] )) {
		sendSysMail ( $request ['uid'], $title, $content );
		//添加log
		$opration_content = '发送给“' . $name . '”一封系统信件';
		sql_query ( "insert into adm_log (`adm_name`,`opration`,`opration_content`,`oprate_time`) values ('$adm_name','send_mesg','$opration_content',unix_timestamp())" );
	}
}
//添加log
$opration_content = '审核了给玩家“' . $name . '”' . $request ['count'] . ' 数量的' . $request ['name'] . '申请';
$ret [] = sql_query ( "insert into adm_log (`adm_name`,`opration`,`opration_content`,`oprate_time`) values ('$adm_name','verify_goods','$opration_content',unix_timestamp())" );

?>