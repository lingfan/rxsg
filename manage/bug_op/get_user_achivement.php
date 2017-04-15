<?php
/**
 * @author 方鸿鹏
 * @method 根据通行证和成就名称获查询用户成就完成情况
 * @param $passport 通行证 $name 成就名称
 * @return 
 *  array{
 * 	'data'=>array{
 * 			uid用户id
 *          aid成就id
 *          record用户完成成就的记录
 * 			}
 *  'error'
 *  }
 * 
 */

	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($passport)){exit("param_not_exist");}
	if (!isset($name)){exit("param_not_exist");}
	
	$aid = sql_fetch_one_cell("select id from cfg_achivement where name='$name'");
	$sql_error = mysql_error();
	if(!empty($aid)&&empty($sql_error)){
		$uid = sql_fetch_one_cell("select uid from sys_user where passport='$passport'");
		$sql_error = mysql_error();
		if(!empty($uid)&&empty($sql_error)){
			$sql = "select u.name as uname,u.passport,c.name as cname,from_unixtime(a.time) as time from sys_user u, cfg_achivement c, sys_user_achivement a 
			where u.uid=a.uid and c.id=a.achivement_id and u.uid='$uid' and c.id='$aid'";
			$ret['data']['record'] = sql_fetch_rows($sql);
			$ret['data']['uid'] = $uid;
			$ret['data']['aid'] = $aid;
			$ret['error'] = mysql_error();
		}
		else{
			$ret['data'] = array();
			$ret['error'] = "账号：<font color='red'>".$passport."</font>不存在！";
		}
	}
	else{
		$ret['data'] = array();
		$ret['error'] = "成就：<font color='red'>".$name."</font>不存在！";
	}
?>