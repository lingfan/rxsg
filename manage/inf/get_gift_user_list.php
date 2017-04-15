<?php
/**
 * @author 张昌彪
 * @模块 查询查看 -- 查询用户
 * @功能 通过玩家id查询礼金信息
 * @参数 $uid 玩家id
 * @返回 
 * array(
 * '0'=>array(
 *      'uid'=>'玩家id'，
 *      'name'=>'君主名',
 *      'passport'=>'账号',
 *      'count'=>'礼金数',
 *      'formattime'=>'记录时间',
 *      'type'=>'操作类型数'
 *      ),
 * '1'=>array(
 *      'uid'=>'玩家id'，
 *      'name'=>'君主名',
 *      'passport'=>'账号',
 *      'count'=>'礼金数',
 *      'formattime'=>'记录时间',
 *      'type'=>'操作类型数'
 *      ),
 * .......
 * )
 * 如果为空 返回 'no data'
 */
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($uid))exit("param_not_exist");
	$userlist = sql_fetch_rows("select u.uid as uid,from_unixtime(time) as formattime,name,passport,`count`,type from log_gift m,sys_user u where u.uid='$uid' and m.uid=u.uid order by formattime");
	
	if(empty($userlist))
	{
		$ret = 'no data';
	}
	else
	{
		foreach($userlist as &$user)
		{
			if($user['type']==10)
			{
				$props = sql_fetch_one("select * from log_shop where uid = $user[uid] and from_unixtime(time) ='$user[formattime]'");
				$user['props'] = $props;
			}
		}
		$ret = $userlist;
	}

?>