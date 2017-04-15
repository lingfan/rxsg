<?php
/**
 * @method 获取玩家使用外挂信息
 * @author 方鸿鹏
 * @param $passport 账号 $name 君主名 $uid 用户id
 * @return 
 */
	if (!defined("MANAGE_INTERFACE")) exit;
	
	if (!isset($name)&&!isset($passport)&&!isset($uid)){exit("param_not_exist");}
	
	if(isset($uid)){
		$sql = "select aa.uid,bb.passport,bb.name,aa.ip,from_unixtime(login_time) as login_time,from_unixtime(update_time) as update_time 
				from log_waigua_online aa left join sys_user bb on aa.uid=bb.uid 
				where bb.uid='$uid' order by login_time desc;";
	}else{
		if(!empty($name)){
			$sql = "select aa.uid,bb.passport,bb.name,aa.ip,from_unixtime(login_time) as login_time,from_unixtime(update_time) as update_time 
					from log_waigua_online aa left join sys_user bb on aa.uid=bb.uid 
					where bb.name='$name' order by login_time desc;";
		}
		else if(!empty($passport)){
			$sql = "select aa.uid,bb.passport,bb.name,aa.ip,from_unixtime(login_time) as login_time,from_unixtime(update_time) as update_time 
					from log_waigua_online aa left join sys_user bb on aa.uid=bb.uid 
					where bb.passport='$passport' order by login_time desc;";
		}
		else{
			$ret = 'on data';exit;
		}
	}
	$ret = sql_fetch_rows($sql);
	$sql_error = mysql_error();
	if(empty($ret)||!empty($sql_error))
		$ret = 'no data';
?>