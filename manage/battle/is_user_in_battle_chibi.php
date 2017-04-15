<?php
/**
 * @author 方鸿鹏
 * @method 跨服战场-判断玩家是否在赤壁跨服战场中
 * @param $name 君主名 $passport 账号 $server 服务器名 
 * @return
 * array{
 * success=>从战场正常开启，一直参与到战场正常结束的玩家人数
 * false=>在赤壁之战结束前，玩家主动退出战场的人数
 * } 
 */

if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($name)) {
    exit("param_not_exist");
}
if (! isset($passport)) {
    exit("param_not_exist");
}
if (! isset($server)) {
    exit("param_not_exist");
}

$from_serverid = sql_fetch_one_cell("select from_serverid from sys_servers where server_name='$server'","chibinet");
if(!empty($from_serverid)){
	if(!empty($passport)){
		$uid = sql_fetch_one_cell("select u.uid from sys_user_battle_state s, sys_user u where s.uid=u.uid and u.passport='$passport' and u.from_serverid='$from_serverid'","chibinet");
		$sql_error = mysql_error();
		if(!empty($uid)&&empty($sql_error)){
			$ret = "服务器：".$server."账号：".$passport."的玩家正在赤壁战场中！";
		}
		else{
			$ret = "<font color='red'>服务器：".$server."账号：".$passport."的玩家不在赤壁战场中！</font>";
		}
	}
	else{
		if(!empty($name)){
			$uid = sql_fetch_one_cell("select u.uid from sys_user_battle_state s, sys_user u where s.uid=u.uid and u.name='$name' and u.from_serverid='$from_serverid'","chibinet");
			if(!empty($uid)&&empty($sql_error)){
				$ret = "服务器：".$server."君主名：".$name."的玩家正在赤壁战场中！";
			}
			else{
				$ret = "<font color='red'>服务器：".$server."君主名：".$name."的玩家不在赤壁战场中！</font>";
			}
		}
	}
}
else{
	$ret = "服务器：".$server."不存在！";
}
?>