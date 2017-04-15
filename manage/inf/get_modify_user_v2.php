<?php
/**
 * @author 阮钰标
 * @模块 查询查看 -- 查询用户
 * @功能 查询用户信息,任务物品
 * @参数 $uid int 用户的uid
 * @返回 array 用户信息，任务物品数组
 *       如果为空就返回no data
 */
    if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($uid))exit("param_not_exist");
	$user_info = sql_fetch_one("select u.nobility,u.officepos,u.uid as uid,name,passport,`group`,state,prestige,rank,union_id,money,lastupdate,lastcid,onlinetime,gift from sys_sessions s,sys_online o,sys_user u where u.uid='$uid' and s.uid=u.uid and s.uid=o.uid");
	$union_id = $user_info['union_id'];
	$officepos = $user_info['officepos'];
	$nobility = $user_info['nobility'];
	$unionlist=sql_fetch_one_cell("select name from sys_union where id='$union_id'");
    $officepos = sql_fetch_one_cell("select name from cfg_office_pos where id='$officepos'");
	$nobility = sql_fetch_one_cell("select name from cfg_nobility where id='$nobility'");
	$user_info['nobility'] = $nobility;
    $user_info['officepos'] = $officepos;
    $user_info['union_id'] = !empty($unionlist)?$unionlist:"尚无联盟";
    if (empty($user_info)) $user_info='no data';
    $ret[] = $user_info;
    
    $taskthings=sql_fetch_rows("select cfg_things.name,sys_things.count from cfg_things , sys_things where sys_things.uid = '$uid' and cfg_things.tid = sys_things.tid and sys_things.count>=0");
	if (empty($taskthings)) $taskthings='no data';
	$ret[]=$taskthings;
?>