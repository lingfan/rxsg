<?php
/**
 * @author 方鸿鹏
 * @method 获得用户跨服七擒勋绩总值，以及勋绩损失记录
 * @param $passport通行证 $server服务器
 * @return 
 * 	array{
 * 		0=>用户信息
 * 		1=>勋绩总值
 * 		2=>勋绩损失记录
 * }
 */
if (! defined("MANAGE_INTERFACE"))
    exit();
if (isset($passport)&&isset($server)) {
	$sql = "select u.uid,u.name,u.passport,s.server_name from sys_user u, sys_servers s 
			where u.from_serverid=s.from_serverid and s.server_name='$server' and u.passport='$passport'";
}
else{
	if(isset($uid)){
		$sql = "select u.uid,u.name,u.passport,s.server_name from sys_user u, sys_servers s 
			where u.from_serverid=s.from_serverid and u.uid='$uid'";
	}
	else{
		exit("param_not_exist");
	}
}
$user = sql_fetch_one($sql,'battlenet_9001');
$sql_error = mysql_error();
if(!empty($user)&&empty($sql_error)){
	$uid = $user['uid'];
	$ret['user']=$user;
	$xunji_total = sql_fetch_one_cell("select gongxun from sys_user_battle_score where uid='$uid'",'battlenet_9001');
	$sql_error = mysql_error();
	if(!empty($xunji_total)&&empty($sql_error)){
		$ret['total']=$xunji_total;
	}
	$record = sql_fetch_rows("select from_unixtime(quittime) as time, gained_score 
			  from bak_sys_user_battle_state where gained_score<0 and uid='$uid'",'battlenet_9001');
	$sql_error = mysql_error();
	if(!empty($record)&&empty($sql_error)){
		$ret['record']=$record;
	}
}
else{
	$ret = 'no data';
}
?>
