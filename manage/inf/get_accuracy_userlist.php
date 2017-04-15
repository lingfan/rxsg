<?php
/**
 * @author 张昌彪
 * @模块 查询查看 -- 查询用户
 * @功能 通过玩家游戏名精确查询用户详细信息
 * @参数 $name 玩家名
 * @返回 
 * array(
 * '0'=>array(
 *      'name'=>'君主名'，
 *      'passport'=>'账号',
 *      'regtime'=>'注册时间',
 *      'union_id'=>'联盟/尚无联盟',
 *      'lastupdate'=>'是否在线',
 *      'lastcid'=>'当前坐标',
 *      'onlinetime'=>'累计在线时间',
 *      'last_time'=>'上次登录时间',
 *      'last_ip'=>'最后一次登陆ip',
 *      'pay_list'=>'充值记录'
 *      )
 * )
 */
	if (!defined("MANAGE_INTERFACE")) exit;
function format_time($long_time)
    {
        if(strstr($long_time, ':'))
            return $long_time;
        if($long_time/3600>=1){
            $hours = intval($long_time/3600);
            if($hours<10)$hours = '0'.$hours;
            $long_time = $long_time%3600;
        }else{$hours='00';}
        if($long_time/60>=1){
            $mins = intval($long_time/60);
            if($mins<10)$mins = '0'.$mins;
            $long_time = $long_time%60;
        }else{$mins='00';}
        $secs =  $long_time;
        if($secs<10)$secs = '0'.$secs;
        return $long_time = $hours.':'.$mins.':'.$secs;
    }
	if (!isset($name))exit("param_not_exist");
	$ret = sql_fetch_rows("select u.nobility,u.officepos,u.uid as uid,name,passport,`group`,state,prestige,rank,union_id,money,lastupdate,lastcid,onlinetime,from_unixtime(regtime) as regtime from sys_sessions s,sys_online o,sys_user u where u.name='$name' and s.uid=u.uid and s.uid=o.uid");
	$userlist = $ret;
	if(isset($userlist)&&!empty($userlist))
	{
		foreach($userlist as &$user)
		{
			$user['unionlist'] = sql_fetch_one_cell("select name from sys_union where id='$user[union_id]'");
			$user['nobility'] = sql_fetch_one_cell("select name from cfg_nobility where id='$user[nobility]'");
			$user['officepos'] = sql_fetch_one_cell("select name from cfg_office_pos where id='$user[officepos]'");
			$user['timenow'] = sql_fetch_one_cell("select unix_timestamp()");
			$user['loginnums'] = sql_fetch_one_cell("select count(uid) from log_login where uid = $user[uid] limit 1");
			$ip = sql_fetch_one_cell("select ip from log_login where uid = $user[uid] order by time desc limit 1");
			$user['last_ip'] = ($ip&0xff).".".(($ip>>8)&0xff).".".(($ip>>16)&0xff).".".(($ip>>24)&0xff);
			$user['last_time'] = sql_fetch_one_cell("select from_unixtime(time) from log_login where uid = $user[uid] order by time desc limit 1");
			$user['pay_list'] = sql_fetch_rows("select payname,passport,passtype,money,from_unixtime(time) as time,code,type,orderid from pay_log where passport = '$user[passport]'");
		}
		if(!empty($userlist)){
			foreach($userlist as &$user)
			{
				$user['union_id'] = !empty($user['unionlist'])?$user['unionlist']:"尚无联盟";
			    $timenow = $user['timenow'];
			    $user['lastupdate'] = ($timenow - $user['lastupdate'] < 60)?"在线":"不在线";
			    $y = intval($user['lastcid']/1000);
			    $x = $user['lastcid']-$y*1000;
			    $user['lastcid'] = "(".$x.",".$y.")";
			    $user['onlinetime'] = format_time($user['onlinetime']);
			}
		}
	}
	if(empty($ret))$ret = 'no data';else $ret = $userlist;
?>