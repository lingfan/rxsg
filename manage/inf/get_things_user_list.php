<?php
/**
 * @author 张昌彪
 * @模块 查询查看 -- 查询用户
 * @功能 通过玩家id查询道具信息
 * @参数 $uid 玩家id
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
	$ret = sql_fetch_rows("select g.uid as uid,count,from_unixtime(time) as formattime,type,passport,u.name as uname,c.name as gname from log_things g,sys_user u,cfg_things c where u.uid='$uid' and g.tid=c.tid and u.uid=g.uid");
	if(empty($ret))$ret = 'no data';

?>