<?php
/**
 * @author 阮钰标
 * @模块 查询查看 -- 查询用户
 * @功能 通过玩家id查询道具信息
 * @参数 $uid 玩家id
 * @参数 $name 道具名称
 * @参数 $starttime 开始时间
 * @参数 $endtime 结束时间
 * @返回 
 * array(
 * '0'=>array(
 *      'uid'=>'玩家id'，
 *      'uname'=>'君主名',
 *      'passport'=>'账号',
 *      'gname'=>'道具名',
 *      'count'=>'道具数量',
 *      'formattime'=>'记录时间',
 *      'type'=>'操作类型数'
 *      ),
 * '1'=>array(
 *      'uid'=>'玩家id'，
 *      'uname'=>'君主名',
 *      'passport'=>'账号',
 *      'gname'=>'道具名',
 *      'count'=>'道具数量',
 *      'formattime'=>'记录时间',
 *      'type'=>'操作类型数'
 *      ),
 * .......
 * )
 */
    if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($uid))exit("param_not_exist");
	if (!isset($name))exit("param_not_exist");
	if (!isset($starttime))exit("param_not_exist");
	if (!isset($endtime))exit("param_not_exist");
	if (empty($starttime)) $starttime=date("Ymd");
	if (empty($endtime)) $endtime = date('Ymd');
	if (empty($name)){
		$ret = sql_fetch_rows("select g.uid as uid,count,from_unixtime(time) as formattime,type,passport,u.name as uname,c.name as gname from log_goods g,sys_user u,cfg_goods c where u.uid='$uid' and g.time >= unix_timestamp($starttime) and g.time < unix_timestamp($endtime)+86400 and g.gid=c.gid and u.uid=g.uid");
	}
	else {
		$ret = sql_fetch_rows("select g.uid as uid,count,from_unixtime(time) as formattime,type,passport,u.name as uname,c.name as gname from log_goods g,sys_user u,cfg_goods c where u.uid='$uid' and c.name='$name' and g.time >= unix_timestamp($starttime) and g.time < unix_timestamp($endtime)+86400 and g.gid=c.gid and u.uid=g.uid");
	}
	if(empty($ret)) $ret = 'no data';

?>