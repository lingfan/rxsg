<?php
//副本，战场
require_once ("./interface.php");
require_once ("./utils.php");
require_once ("./GoodsFunc.php");
require_once ("./UnionFunc.php");
require_once ("../config/db.php");
require_once './ActFunc.php';
require_once ("BattleNetFunc.php");
/**
 * 任何战场行动以前取得战场信息
 *
 * @param unknown_type $uid
 * @param unknown_type $param
 * @param unknown_type $emptythrow
 * @return unknown
 */
function firstGetUserBattleInfo($uid, $emptythrow = 1) {
	//cfg_battle_field 里 读name
	//sys_user_battle_field里读 bid,createuid,level,maxpeople,endtime,type,state
	//sys_user_battle_state里读 battlefieldid,honour,unionid,startcid
	$userbattleinfo = sql_fetch_one ( "select su.honour,u.unionid,u.battlefieldid,b.bid,b.createuid,b.level,b.maxpeople,b.starttime,b.endtime,b.type,b.state,b.winner,u.in_cross_battle,c.name
	from sys_user_battle_state u left join sys_user_battle_field b on u.battlefieldid=b.id left join cfg_battle_field c on b.bid=c.id left join sys_user su on u.uid=su.uid where u.state=0 AND u.uid='$uid'" );
	if (empty ( $userbattleinfo )) {
		if ($emptythrow)
			throw new Exception ( $GLOBALS ['battle'] ['user_not_in_battle'] );
		return $userbattleinfo;
	}
	if ($userbattleinfo ["state"] == 1) {
		throw new Exception ( $GLOBALS ['battle'] ['battle_froze'] );
	}
	
	return $userbattleinfo;
}

function questInCity($uid, $param) {
	$hid = intval(array_shift ( $param ));
	$cid = intval(array_shift ( $param ));
	$battleinfo = firstGetUserBattleInfo ( $uid );
	$ret = array ();
	//检查是否有军队在改城池，否则不能探索
	$cityinfo = sql_fetch_one ( "select can_quest from sys_battle_city where cid='$cid'" );
	$ahid = sql_fetch_one_cell ( "SELECT b.hid FROM sys_battle_city a,sys_troops b WHERE a.battlefieldid=$battleinfo[battlefieldid] AND a.cid=$cid AND a.cid=b.cid AND b.uid=$uid limit 1" );
	if ($cityinfo ["can_quest"] == 1 && ! empty ( $ahid )) {
		if ($hid == 0) {
			$hid = $ahid;
		}
		//检查上次探索的时间
		if (enoughIntervalFromLastMark ( $battleinfo ["battlefieldid"], 1, $cid, true, 60 )) {
			sendBattleEvent ( 0, $uid, $battleinfo ["bid"], $battleinfo ["battlefieldid"], $battleinfo ["unionid"], $cid % 1000, 11 );
			$ret [] = sql_fetch_one ( "select * from sys_city_hero where hid=$hid" );
			$acid = $cid % 1000;
			$ret [] = sql_fetch_one_cell ( "select msg from cfg_battle_event where bid=$battleinfo[bid] and  triggertype=11 AND targettype=0 and triggerid=$acid limit 1" );
		} else {
			throw new Exception ( $GLOBALS ['battle'] ['too_frequent_quest'] );
		}
	} else {
		throw new Exception ( $GLOBALS ['battle'] ['can_not_quest'] );
	}
	return $ret;
}
/**
 * 判断用户的战场是否已经不能行动
 *
 * @param unknown_type $uid
 */
function ifFrozeExit($uid) {
	$userbattleinfo = sql_fetch_one ( "select s.unionid,s.battlefieldid,su.honour,f.type,f.state,f.bid,f.progress,f.level from sys_user_battle_state s left join sys_user_battle_field f on s.battlefieldid=f.id left join sys_user su on s.uid=su.uid where s.state=0 and s.uid=$uid" );
	if (empty ( $userbattleinfo )) {
		throw new Exception ( $GLOBALS ['battle'] ['user_not_in_battle'] );
	}
	if ($userbattleinfo ["state"] == 1) {
		throw new Exception ( $GLOBALS ['battle'] ['battle_froze'] );
	}
	return $userbattleinfo;
}

function refreshUserBattleState($uid, $param) {
	try {
		$currentbattle = firstGetUserBattleInfo ( $uid );
		$ret = array ();
		$ret [] = 1;
		$ret [] = $currentbattle;
		//$cityinfo=array();
		$cityinfo = sql_fetch_rows ( "select * from sys_battle_city where battlefieldid='$currentbattle[battlefieldid]' " );
		$cityinfo = setFlags ( $uid, $currentbattle ['unionid'], $cityinfo );
		$ret [] = $cityinfo;
		$ret [] = sql_fetch_rows ( "select t.state,t.targetcid,t.soldiers,t.cid,t.id,t.ulimit,h.face,h.sex,h.name as heroname,h.level,h.hid,h.herotype from sys_troops t left join sys_city_hero h on t.hid=h.hid where t.uid='$uid' and t.battlefieldid='$currentbattle[battlefieldid]' order by t.id asc" );
		$ret [] = getBattleStartCityInfo ( $currentbattle ['bid'], $currentbattle ['unionid'] );
		if ($currentbattle ['bid'] == 2001 || $currentbattle ['bid'] == 7001) {
			//官渡之战
			$ret [] = sql_fetch_rows ( "select * from sys_battle_winpoint where battlefieldid='$currentbattle[battlefieldid]'" );
		}
		return $ret;
	} catch ( Exception $e ) {
		$ret = array ();
		$ret [] = 0;
		return $ret;
	}
}

function refreshBattleTaskState($uid,$param) {
//	$bid = array_shift($param);
//	$alarmTids = sql_fetch_one_cell("select group_concat(distinct tid) from cfg_task_cid where bid='$bid'");
//	$alarmTids = '';
//	if ($bid==9001) {
//		$alarmTids='60901,60902,60903,60904,60905,60906,60907,60908,60909,60910,60911,60912,60913,60914,60915,60916,60917,60918,60919';
//	}
	$alarmTids='60901,60902,60903,60904,60905,60906,60907,60908,60909,60910,60911,60912,60913,60914,60915,60916,60917,60918,60919';
	
	$tids = sql_fetch_rows("select tid as id from sys_user_task where uid='$uid' and state=0 and tid in ($alarmTids)");
	checkTaskComplete($uid,$tids);
	$finishedTidStr='0,';
	$allTidStr='0,';
	foreach ($tids as $taskState) {
		if ($taskState['state']) {
			$finishedTidStr.=$taskState['id'].",";
		}
		$allTidStr.=$taskState['id'].",";
		
	}
	$finishedTidStr=substr($finishedTidStr,0,-1);
	$allTidStr=substr($allTidStr,0,-1);
	$ret = sql_fetch_rows("select cid, min(state) as state from ( select cid, case when tid in ($finishedTidStr) then 3 else type end as state from cfg_task_cid where tid in ($allTidStr)) a group by cid");
	return $ret;
}

function getLastMainTask($uid,$param) {
	$bid = array_shift($param);
	$ret = array();
	if ($bid==9001) {
		$ret=sql_fetch_rows("select cfg.name,cfg.todo,cfg.id,goal.count,goal.type,goal.sort from sys_user_task sys left join cfg_task cfg on sys.tid=cfg.id left join cfg_task_goal goal on goal.tid=cfg.id where sys.uid=$uid and sys.tid between 60901 and 60914");
//		$tid = sql_fetch_one_cell("select tid from sys_user_task where uid='$uid' and tid between 60901 and 60914 order by tid desc limit 1");
//		$task = sql_fetch_one("select name, todo from cfg_task where id='$tid'");
//		$goal=sql_fetch_one("select type,count from cfg_task_goal where tid='$tid' limit 1");
//		$ret[] = $task['name'];
//		$ret[] = $task['todo'];
//		$ret[] = $goal['type'];
//		$ret[] = $goal['count'];
	}
	return $ret;
}

function getUserBattleInfo($uid) {
	$ret = array ();
	$info1=array();
	$info2=array();
	$now = sql_fetch_one_cell("select unix_timestamp()");
	$currentbattle = sql_fetch_one("select in_cross_battle,bid,name,unix_timestamp(jointime) as starttime from sys_user_battle_state a,cfg_battle_field b where a.bid=b.id and uid=$uid");
	if (empty ( $currentbattle )) {
		$info1 [] = 0;
		if(defined("BATTLE_NET_ENABLE") && BATTLE_NET_ENABLE) {
			try {
				$crossRet = sendRemoteRequest($uid, "getUserCrossWaitingState");
			} catch (Exception $e) {
				$crossRet = array(0);
			}
			$crossRet[]=0; // pvp battlenet
			try {
				$cross9001Ret = sendRemote9001Request($uid, "getUserCrossWaitingState");
			} catch (Exception $e) {
				$cross9001Ret = array(0);
			}
			$cross9001Ret[]=1; // pve battlenet
			if((count($crossRet) <= 2) && (count($cross9001Ret) <= 2))
				$info1 = array_merge($info1,array(0,-1));
			else if(count($crossRet) > 2)
				$info1 = array_merge($info1,$crossRet);
			else if(count($cross9001Ret) > 2)
				$info1 = array_merge($info1,$cross9001Ret);
		}
		else {
			$info1 [] = 0;
			$info1 [] = -1; // unknown battlenet type
		}
	}
	else {
		$info1 [] = 1;
		if($currentbattle ["in_cross_battle"] == 1) {
			$info1 [] = 1;
		}
		else {
			$info1 [] = 0;
		}
		$info1[] = $currentbattle['bid'];
		$info1[] = $currentbattle['name'];
		if($currentbattle['bid']==6001){
			//不从远程取了，太费了
			if($now-intval($currentbattle['starttime'])>1800){
				$info1[]=1;
			}else{
				$info1[]=0;
			}
		}
	}
   
	$currentchibi = sql_fetch_one("select * from sys_user_chibi_state where uid=$uid");
	if (empty ( $currentchibi )) {
		$info2 [] = 0;
		if(defined("CHIBI_NET_ENABLE") && CHIBI_NET_ENABLE) {
			try {
				$chibiRet = sendChibiRemoteRequest($uid, "getUserChibiWaitingState");
			} catch (Exception $e){
				$chibiRet = array(0);
			}
			$info2=array_merge($info2,$chibiRet);
		}
		else {
			$info2 [] = 0;
		}
	}
	else {
		$info2 [] = 1;
		$info2 [] = sql_fetch_one_cell("select name from cfg_battle_field where id=11000");
	}
	
	 $ret[]=$info1;
	 $ret[]=$info2;
	return $ret;
}

function getBattleInfo($uid, $param) {
	sql_query ( "update sys_alarm set battle=0 where uid=$uid" );
	$nobility = intval ( sql_fetch_one_cell ( "select nobility from sys_user  where uid = $uid" ) );
	//推恩
	$nobility = getBufferNobility ( $uid, $nobility );
	if ($nobility < 1)
		throw new Exception ( $GLOBALS ['battle'] ['nobility_not_rearch'] );
	$currentbattle = firstGetUserBattleInfo ( $uid, 0 );
	$ret = array ();
	//当前战场客户端版本
	$ret [] = sql_fetch_one_cell ( "select value from mem_state where state=30" );
	//if (empty ( $currentbattle )) {
		
		//沒有參加過任何戰役
		
		//$battlenetinfo = sendRemoteRequest ( $uid, "getUserBattleState" );
		//if ($battlenetinfo [0] == 2) {
		//	$ret [] = 2;
		//}else{
		//	$ret [] = 0;
		//}
		$ret [] = 0;
		$cfgbattles = sql_fetch_rows ( "select * from cfg_battle_field where state != 0 and type in (0,1,3,4,10)" );
		$nowtimestamp = sql_fetch_one_cell ( "select unix_timestamp()" );
		$act = sql_fetch_one ( "select * from cfg_act_battle where state>0 and (((weekday(from_unixtime($nowtimestamp))+1)=actid and hour(from_unixtime($nowtimestamp)) between starthour and endhour-1) || (date>0 and $nowtimestamp between date+starthour*3600 and date+endhour*3600)) limit 1" );
		foreach ( $cfgbattles as $cfgbattle ) {
			/*
			 * 战场倍率活动
			 */
			$baseRate = 1;
			if (! empty ( $act )) {
				if ($act ["state"] == 1) {
					$baseRate = $act ["rate"];
				} else if ($act ["state"] == 2 && sql_fetch_one_cell ( "select count(*) from cfg_act_battle_details where actid=$act[actid] and bid=$cfgbattle[id]" )) {
					$baseRate = $act ["rate"];
				}
			}
			$cfgbattle ["hasBattleAct"] = $baseRate == 1 ? false : true;
			if ($cfgbattle ['type'] == 0) {
				$battlecount = sql_fetch_one_cell ( "select count(*) from sys_user_battle_field where bid='$cfgbattle[id]' and state=0" );
				//判断战场是否已满，满了就灰掉
				if ($battlecount >= $cfgbattle ['maxcount']) {
					$cfgbattle ['canCreate'] = false;
				} else {
					$cfgbattle ['canCreate'] = true;
				}
			}
			$ret [2] [] = $cfgbattle;
		}
		//$gongxun=getBattleNetExploit($uid);
		//if($gongxun==-1)$gongxun=0;
		//	$ret[]=$gongxun;
		//功勋和等级，这里不读取
		$ret[]=0;
		$ret[]=0;
		
		$maxWarCount = 5;
		$todayWarCount = sql_fetch_one_cell ( "select today_war_count from mem_user_schedule where uid = $uid" );
		if (empty ( $todayWarCount ))
			$todayWarCount = 0;

		$ret [] = $todayWarCount;
		$ret [] = 5;
		
	//		$ret [] = sql_fetch_rows ( "SELECT a.*,b.battleBelong,b.name as medalName FROM cfg_shop a,cfg_things b WHERE onsale=1 AND starttime<=UNIX_TIMESTAMP() AND endtime>UNIX_TIMESTAMP() AND (`group`=6 or `battleshop`= 1) AND a.medalTypeId=b.tid ORDER BY POSITION,id" );
	//		$ret [] = sql_fetch_rows ( "SELECT a.* FROM cfg_armor a,cfg_shop b WHERE `onsale`=1 AND `group`=6 AND a.id=b.gid AND battlegoodstype=1" );
	//		$ret [] = sql_fetch_rows ( "select * from cfg_hero where id in (select gid from cfg_shop where `onsale`=1 and `group`=6)" );
//	} 
//	else {
//		$ret [] = 1;
//		if ($currentbattle ["in_cross_battle"] == 1) {
//			$ret [] = 1;
//			return $ret;
//		}else{
//			$ret[] = 0;
//		}
//		$ret [] = $currentbattle;
//		//$cityinfo=array();
//		$cityinfo = sql_fetch_rows ( "select * from sys_battle_city where battlefieldid='$currentbattle[battlefieldid]' " );
//		$cityinfo = setFlags ( $uid, $currentbattle ['unionid'], $cityinfo );
//		$ret [] = $cityinfo;
//		$ret [] = sql_fetch_rows ( "select t.state,t.targetcid,t.soldiers,t.cid,t.id,h.face,h.sex,h.name as heroname,h.level,h.hid from sys_troops t left join sys_city_hero h on t.hid=h.hid where t.uid='$uid' and t.battlefieldid='$currentbattle[battlefieldid]' order by t.id asc" );
//		$ret [] = getBattleStartCityInfo ( $currentbattle ['bid'], $currentbattle ['unionid'] );
//		if ($currentbattle ['bid'] == 2001 || $currentbattle ['bid'] == 7001) {
//			//官渡之战
//			
//
//			$ret [] = sql_fetch_rows ( "select * from sys_battle_winpoint where battlefieldid='$currentbattle[battlefieldid]'" );
//		}
//	}
	return $ret;
}

function getChibiInfo($uid, $param) {
	$enterChibi = array_shift($param);
	sql_query ( "update sys_alarm set battle=0 where uid=$uid" );
	$nobility = intval ( sql_fetch_one_cell ( "select nobility from sys_user  where uid = $uid" ) );
	//推恩
	$nobility = getBufferNobility ( $uid, $nobility );
	if ($nobility < 1)
		throw new Exception ( $GLOBALS ['battle'] ['nobility_not_rearch'] );
	$currentbattle = firstGetUserBattleInfo ( $uid, 0 );
	$ret = array ();
	//当前战场客户端版本
	$ret [] = sql_fetch_one_cell ( "select value from mem_state where state=30" );
	//if (empty ( $currentbattle )) {
		
		//沒有參加過任何戰役
		
		//$battlenetinfo = sendRemoteRequest ( $uid, "getUserBattleState" );
		//if ($battlenetinfo [0] == 2) {
		//	$ret [] = 2;
		//}else{
		//	$ret [] = 0;
		//}
		if($enterChibi)
			$ret [] = 1;
		else
			$ret [] = 0;
		$cfgbattles = sql_fetch_rows ( "select * from cfg_battle_field where state != 0 and type in (0,1,3,4,10)" );
		$nowtimestamp = sql_fetch_one_cell ( "select unix_timestamp()" );
		$act = sql_fetch_one ( "select * from cfg_act_battle where state>0 and (((weekday(from_unixtime($nowtimestamp))+1)=actid and hour(from_unixtime($nowtimestamp)) between starthour and endhour-1) || (date>0 and $nowtimestamp between date+starthour*3600 and date+endhour*3600)) limit 1" );
		foreach ( $cfgbattles as $cfgbattle ) {
			/*
			 * 战场倍率活动
			 */
			$baseRate = 1;
			if (! empty ( $act )) {
				if ($act ["state"] == 1) {
					$baseRate = $act ["rate"];
				} else if ($act ["state"] == 2 && sql_fetch_one_cell ( "select count(*) from cfg_act_battle_details where actid=$act[actid] and bid=$cfgbattle[id]" )) {
					$baseRate = $act ["rate"];
				}
			}
			$cfgbattle ["hasBattleAct"] = $baseRate == 1 ? false : true;
			if ($cfgbattle ['type'] == 0) {
				$battlecount = sql_fetch_one_cell ( "select count(*) from sys_user_battle_field where bid='$cfgbattle[id]' and state=0" );
				//判断战场是否已满，满了就灰掉
				if ($battlecount >= $cfgbattle ['maxcount']) {
					$cfgbattle ['canCreate'] = false;
				} else {
					$cfgbattle ['canCreate'] = true;
				}
			}
			$ret [2] [] = $cfgbattle;
		}
		//$gongxun=getBattleNetExploit($uid);
		//if($gongxun==-1)$gongxun=0;
		//	$ret[]=$gongxun;
		//功勋和等级，这里不读取
		$ret[]=0;
		$ret[]=0;
		
		$maxWarCount = 5;
		$todayWarCount = sql_fetch_one_cell ( "select today_war_count from mem_user_schedule where uid = $uid" );
		if (empty ( $todayWarCount ))
			$todayWarCount = 0;

		$ret [] = $todayWarCount;
		$ret [] = 5;
		
	//		$ret [] = sql_fetch_rows ( "SELECT a.*,b.battleBelong,b.name as medalName FROM cfg_shop a,cfg_things b WHERE onsale=1 AND starttime<=UNIX_TIMESTAMP() AND endtime>UNIX_TIMESTAMP() AND (`group`=6 or `battleshop`= 1) AND a.medalTypeId=b.tid ORDER BY POSITION,id" );
	//		$ret [] = sql_fetch_rows ( "SELECT a.* FROM cfg_armor a,cfg_shop b WHERE `onsale`=1 AND `group`=6 AND a.id=b.gid AND battlegoodstype=1" );
	//		$ret [] = sql_fetch_rows ( "select * from cfg_hero where id in (select gid from cfg_shop where `onsale`=1 and `group`=6)" );
//	} else {
//		$ret [] = 1;
//		if ($currentbattle ["in_cross_battle"] == 1) {
//			$ret [] = 1;
//			return $ret;
//		}else{
//			$ret[] = 0;
//		}
//		$ret [] = $currentbattle;
//		//$cityinfo=array();
//		$cityinfo = sql_fetch_rows ( "select * from sys_battle_city where battlefieldid='$currentbattle[battlefieldid]' " );
//		$cityinfo = setFlags ( $uid, $currentbattle ['unionid'], $cityinfo );
//		$ret [] = $cityinfo;
//		$ret [] = sql_fetch_rows ( "select t.state,t.targetcid,t.soldiers,t.cid,t.id,h.face,h.sex,h.name as heroname,h.level,h.hid from sys_troops t left join sys_city_hero h on t.hid=h.hid where t.uid='$uid' and t.battlefieldid='$currentbattle[battlefieldid]' order by t.id asc" );
//		$ret [] = getBattleStartCityInfo ( $currentbattle ['bid'], $currentbattle ['unionid'] );
//		if ($currentbattle ['bid'] == 2001 || $currentbattle ['bid'] == 7001) {
//			//官渡之战
//			
//
//			$ret [] = sql_fetch_rows ( "select * from sys_battle_winpoint where battlefieldid='$currentbattle[battlefieldid]'" );
//		}
//	}
	return $ret;
}

/**
 * 取得用户是否处在战场中的信息，如果没有则给用户打开选择战场对话框。
 * 如果有则给用户他当前战场的最新信息。
 *
 * @param unknown_type $uid
 * @param unknown_type $param
 * @return unknown
 */
function getUserBattleState($uid, $param) {
	$ret = array ();
	//当前战场客户端版本
	$ret [] = sql_fetch_one_cell ( "select value from mem_state where state=30" );
	
	sql_query ( "update sys_alarm set battle=0 where uid=$uid" );
	$nobility = intval ( sql_fetch_one_cell ( "select nobility from sys_user  where uid = $uid" ) );
	//推恩
	$nobility = getBufferNobility ( $uid, $nobility );
	if ($nobility < 1)
		throw new Exception ( $GLOBALS ['battle'] ['nobility_not_rearch'] );
	$currentbattle = firstGetUserBattleInfo ( $uid, 0 );
	$ret = array ();
	//当前战场客户端版本
	$ret [] = sql_fetch_one_cell ( "select value from mem_state where state=30" );
	
	$waitingBid = 0;
	if (empty ( $currentbattle )) {
		$waitingBid = sql_fetch_one_cell("select bid from sys_battlenet_waiting_queue where uid='$uid'");
	}
	
	
	if (empty ( $currentbattle )) {
		
		//沒有參加過任何戰役
		
		//$battlenetinfo = sendRemoteRequest ( $uid, "getUserBattleState" );
		//if ($battlenetinfo [0] == 2) {
		//	$ret [] = 2;
		//}else{
		//	$ret [] = 0;
		//}
		if ($waitingBid>0) {
			$ret [] = 2;
		}else{
			$ret [] = 0;
		}
		$cfgbattles = sql_fetch_rows ( "select * from cfg_battle_field where state != 0 and type in (0,1,3,4,10)" );
		$nowtimestamp = sql_fetch_one_cell ( "select unix_timestamp()" );
		$act = sql_fetch_one ( "select * from cfg_act_battle where state>0 and (((weekday(from_unixtime($nowtimestamp))+1)=actid and hour(from_unixtime($nowtimestamp)) between starthour and endhour-1) || (date>0 and $nowtimestamp between date+starthour*3600 and date+endhour*3600)) limit 1" );
		foreach ( $cfgbattles as $cfgbattle ) {
			/*
			 * 战场倍率活动
			 */
			$baseRate = 1;
			if (! empty ( $act )) {
				if ($act ["state"] == 1) {
					$baseRate = $act ["rate"];
				} else if ($act ["state"] == 2 && sql_fetch_one_cell ( "select count(*) from cfg_act_battle_details where actid=$act[actid] and bid=$cfgbattle[id]" )) {
					$baseRate = $act ["rate"];
				}
			}
			$cfgbattle ["hasBattleAct"] = $baseRate == 1 ? false : true;
			if ($cfgbattle ['type'] == 0) {
				$battlecount = sql_fetch_one_cell ( "select count(*) from sys_user_battle_field where bid='$cfgbattle[id]' and state=0" );
				//判断战场是否已满，满了就灰掉
				if ($battlecount >= $cfgbattle ['maxcount']) {
					$cfgbattle ['canCreate'] = false;
				} else {
					$cfgbattle ['canCreate'] = true;
				}
			}
			$ret [2] [] = $cfgbattle;
		}
		//$gongxun=getBattleNetExploit($uid);
		//if($gongxun==-1)$gongxun=0;
		//	$ret[]=$gongxun;
		//功勋和等级，这里不读取
		$ret[]=0;
		$ret[]=0;
		
		$maxWarCount = 5;
		$todayWarCount = sql_fetch_one_cell ( "select today_war_count from mem_user_schedule where uid = $uid" );
		if (empty ( $todayWarCount ))
			$todayWarCount = 0;

		$ret [] = $todayWarCount;
		$ret [] = 5;
		$ret []=getUserPVENetLevel($uid);
		$ret [] = $waitingBid;

		
	//		$ret [] = sql_fetch_rows ( "SELECT a.*,b.battleBelong,b.name as medalName FROM cfg_shop a,cfg_things b WHERE onsale=1 AND starttime<=UNIX_TIMESTAMP() AND endtime>UNIX_TIMESTAMP() AND (`group`=6 or `battleshop`= 1) AND a.medalTypeId=b.tid ORDER BY POSITION,id" );
	//		$ret [] = sql_fetch_rows ( "SELECT a.* FROM cfg_armor a,cfg_shop b WHERE `onsale`=1 AND `group`=6 AND a.id=b.gid AND battlegoodstype=1" );
	//		$ret [] = sql_fetch_rows ( "select * from cfg_hero where id in (select gid from cfg_shop where `onsale`=1 and `group`=6)" );
	} else {
		$ret [] = 1;
		if ($currentbattle ["in_cross_battle"] == 1) {
			$ret [] = 1;
			$ret [] = sql_fetch_one_cell("select bid from sys_user_battle_state where uid='$uid' limit 1");
			return $ret;
		}else{
			$ret[] = 0;
		}
		$ret [] = $currentbattle;
		//$cityinfo=array();
		$cityinfo = sql_fetch_rows ( "select * from sys_battle_city where battlefieldid='$currentbattle[battlefieldid]' " );
		$cityinfo = setFlags ( $uid, $currentbattle ['unionid'], $cityinfo );
		$ret [] = $cityinfo;
		$ret [] = sql_fetch_rows ( "select t.state,t.targetcid,t.soldiers,t.cid,t.id,h.face,h.sex,h.name as heroname,h.level,h.hid,h.herotype from sys_troops t left join sys_city_hero h on t.hid=h.hid where t.uid='$uid' and t.battlefieldid='$currentbattle[battlefieldid]' order by t.id asc" );
		$ret [] = getBattleStartCityInfo ( $currentbattle ['bid'], $currentbattle ['unionid'] );
		if ($currentbattle ['bid'] == 2001 || $currentbattle ['bid'] == 7001) {
			//官渡之战
			

			$ret [] = sql_fetch_rows ( "select * from sys_battle_winpoint where battlefieldid='$currentbattle[battlefieldid]'" );
		}
		$ret []=getUserPVENetLevel($uid);
	}
	
	return $ret;
}

function getUserBattleShopInfoForChiBi($uid,$param){
	return getUserBattleShopInfo($uid,$param);
}

function getUserBattleShopInfo($uid, $param) {
	
	$ret = array ();
	$currentbattle = firstGetUserBattleInfo ( $uid, 0 );
	if (empty ( $currentbattle )) {
		
		$nobility = intval ( sql_fetch_one_cell ( "select nobility from sys_user  where uid = $uid" ) );
		
		$nobility = getBufferNobility ( $uid, $nobility );
		if ($nobility < 1) {
			$ret [0] = 1;
		} else {
			$ret [0] = 0;
		}
		//还是要判断下是否在等待队列
		//$battlenetinfo = sendRemoteRequest ( $uid, "getUserBattleState" );
		//if ($battlenetinfo [0] == 2) {
		//	$ret [0] = 1;
		//}
	} else {
		$ret [0] = 1;
	}
	if((defined("BATTLE_NET_ENABLE") && BATTLE_NET_ENABLE) && (defined("CHIBI_NET_ENABLE") && CHIBI_NET_ENABLE))
		$cfgbattles = sql_fetch_rows ( "select * from cfg_battle_field where state !=0" );
	else if (defined("CHIBI_NET_ENABLE") && CHIBI_NET_ENABLE) {
		$cfgbattles = sql_fetch_rows ( "select * from cfg_battle_field where state !=0 and (type= 0 or type = 1 or type=10 or type=5)" );
	}
	else if (defined("BATTLE_NET_ENABLE") && BATTLE_NET_ENABLE) {
		$cfgbattles = sql_fetch_rows ( "select * from cfg_battle_field where state !=0 and type in (0,1,3,4,5)" );
	}
	else {
		$cfgbattles=sql_fetch_rows("select * from cfg_battle_field where state != 0 and (type = 0 or type = 1 or type=5)");	
	}
//	if(defined("BATTLE_NET_ENABLE") && BATTLE_NET_ENABLE)
//		$cfgbattles=sql_fetch_rows("select * from cfg_battle_field where state != 0 ");	
//	else 
//		$cfgbattles=sql_fetch_rows("select * from cfg_battle_field where state != 0 and (type = 0 or type = 1)");		
	foreach($cfgbattles as $cfgbattle){
		if($cfgbattle['type']==0){
			$battlecount=sql_fetch_one_cell("select count(*) from sys_user_battle_field where bid='$cfgbattle[id]' and state=0");
			//判断战场是否已满，满了就灰掉
			if($battlecount>=$cfgbattle['maxcount']){
				$cfgbattle['canCreate']=false;
			}else{
				$cfgbattle['canCreate']=true;
			}
		}
		$ret[1][]=$cfgbattle;
	}
	
	$maxWarCount=5;
	$todayWarCount = sql_fetch_one_cell("select today_war_count from mem_user_schedule where uid = $uid");
	if (empty($todayWarCount))$todayWarCount=0;
	$gongxun=getBattleNetExploit($uid);
	if($gongxun==-1)$gongxun=0;
	$ret[]=$gongxun;
	$ret[]=getUserBattleNetArea($uid);
	$ret[]=$todayWarCount;
	$ret[]=5;
 
	if((defined("BATTLE_NET_ENABLE") && BATTLE_NET_ENABLE) && (defined("CHIBI_NET_ENABLE") && CHIBI_NET_ENABLE))
		$battleIdAry = sql_fetch_rows ( "select id from cfg_battle_field where state !=0" );
	else if (defined("CHIBI_NET_ENABLE") && CHIBI_NET_ENABLE) {
		$battleIdAry = sql_fetch_rows ( "select id from cfg_battle_field where state !=0 and (type= 0 or type = 1 or type=10 or type=5)" );
	}
	else if (defined("BATTLE_NET_ENABLE") && BATTLE_NET_ENABLE) {
		$battleIdAry = sql_fetch_rows ( "select id from cfg_battle_field where state !=0 and type in (0,1,3,4,5)" );
	}
	else {
		$battleIdAry = sql_fetch_rows ( "select id from cfg_battle_field where state !=0 and (type = 0 or type = 1 or type=5)");
	}
//	if(defined("BATTLE_NET_ENABLE") && BATTLE_NET_ENABLE)
//		$battleIdAry = sql_fetch_rows ( "select id from cfg_battle_field where state !=0" );
//	else
//		$battleIdAry = sql_fetch_rows ( "select id from cfg_battle_field where state !=0 and (type = 0 or type = 1)");
	$battleStr = "(1";
	$isinmenghuo=false;
	foreach ( $battleIdAry as $battleId ) {
		if($battleId['id']==11000){//如果赤壁上了的话就加上赤壁商城
			$battleStr.=",2";
		}
		if($battleId['id']==9001){//如果在孟获
			$isinmenghuo=true;
		}
		$battleStr = $battleStr . "," . $battleId ['id'];
	}
	$battleStr = $battleStr . ")";
	$menghuoshopgoods =array();
	if($isinmenghuo){//孟获商品
		$menghuoshopgoods=sql_fetch_rows ( "SELECT a.*,'3' as battleBelong, '元宝'   as medalName FROM cfg_shop a WHERE a.onsale=1  AND (a.`group`=6 or a.`group`=7 or a.`battleshop`= 1) and (id>=41001 and id<=41009)  ORDER BY a.position, a.gid " );
//		$menghuoshopgoods=sendRemote9001Request($uid,"getMenghuoBattleShop");
	}
	$localShop=sql_fetch_rows ( "SELECT a.*,b.battleBelong,b.name as medalName FROM cfg_shop a,cfg_things b WHERE a.onsale=1  AND (a.`group`=6 or a.`group`=7 or a.`battleshop`= 1) AND a.medalTypeId=b.tid and (b.battleBelong in $battleStr ) and (id<41001 or id>41009) ORDER BY a.position, a.gid" );//id<41001 or id>41009是孟获道具，孟获道具不是用功勋买，在thing里面没有对应的 东西，所以单独拿出来处理
	if(count($menghuoshopgoods)>=1){
		$ret [] =array_merge($menghuoshopgoods,$localShop);		
	}else{
		$ret [] =$localShop;	
	}

	$ret [] = sql_fetch_rows ( "SELECT a.* FROM cfg_armor a,cfg_shop b WHERE b.`onsale`=1 AND (b.`group`=6 or b.`group`=7) AND a.id=b.gid AND b.battlegoodstype=1 and a.bid != 1 and a.bid in $battleStr " );
	$ret [] = sql_fetch_rows ( "select a.* from cfg_hero a,cfg_shop b where b.`onsale`=1 AND (b.`group`=6 or b.`group`=7) AND a.id=b.gid AND b.battlegoodstype=2" );
	$ret [] = intval ( sql_fetch_one_cell ( "select honour from sys_user  where uid = '$uid'" ) );
	$achievementID=sql_fetch_one_cell("select max(achivement_id) from sys_user_achivement where uid='$uid' and achivement_id between 70022 and 70024");
	if($achievementID==70024){
		$level=4;
	}else if($achievementID==70023){
		$level=3;
	}else if($achievementID==70022){
		$level=2;
	}else {
		$level=1;
	}
	$ret [] = $level;
	$shachangscore=sql_fetch_one_cell("select `count` from sys_things where tid=104001 and uid=$uid");
	if(empty($shachangscore)){
		$shachangscore=0;
	}
	$ret[]=$shachangscore;
	return $ret;
}

function getChibiGongXun($uid,$param=null){
	try {
		$gongxun=sendChibiRemoteRequest($uid,"getGongXun");
	}
	catch (Exception $e){
		$gongxun = 0;
	}
	if($gongxun==-1)$gongxun=0;
	$ret=array();
	$ret[]=$gongxun;
	return $ret;
}
/**
 * add by jun zhao
 * @param $uid
 * @param $param
 * @return unknown_type
 */


function getBattleNetExploit($uid)
{
	try {
		return sendRemoteRequest ( $uid, "getUserGongxun" );
	} catch ( Exception $e ) {
		return -1;
	}
	return -1;
}
function getBattleNetPVEGongxun($uid)
{
	try {
		return sendRemote9001Request ( $uid, "getBattleNetPVEGongxun" );
	} catch ( Exception $e ) {
		return -1;
	}
	return -1;
}

function deductUserPVEGongxun($uid,$param)
{
	try {
		return sendRemote9001Request ( $uid, "consumePVEBattleScore",$param );
	} catch ( Exception $e ) {
		return -1;
	}
	return -1;
}
function subtractBattleNetExpoit($uid,$count)
{
	try {
		return sendRemoteRequest ( $uid, "consumeUserGongxun",$count );
	} catch ( Exception $e ) {
		return -1;
	}
	return -1;
}

function getUserBattleNetArea($uid)
{
	try {
		return sendRemoteRequest($uid,"getUserAreaLevel");
	} catch ( Exception $e ) {
		return -1;
	}
	return -1;
}

function isChibiArmor($good){
	if($good){
		if($good['medalTypeId']==60015||$good['medalTypeId']==60016){
			return true;
		}
	}
	return false;
}

function buyBattleGoods($uid, $param) {
	$id = intval(array_shift ( $param ));
	$cnt = intval ( array_shift ( $param ) );
	$battleGoodsType = array_shift ( $param );
	$cityId = array_shift ( $param );
	if ($cnt < 1)
	throw new Exception ( $GLOBALS ['buyGoods'] ['invalid_amount'] );

	if ($cnt < 1)
	throw new Exception ( $GLOBALS ['buyGoods'] ['invalid_amount'] );
	$goods = sql_fetch_one ( "select * from cfg_shop where id='$id' and onsale=1 and starttime<=unix_timestamp() and endtime>unix_timestamp()" );
	if (empty ( $goods ))
	throw new Exception ( $GLOBALS ['buyGoods'] ['stop_sale'] );
	$isCBArmor=isChibiArmor($goods);
	$creditNeed = $cnt * $goods ['creditPrice'];
	$medalNeed = $cnt * $goods ['medalPrice'];
	$medalTypeId = $goods ['medalTypeId'];
	$battleBelong=sql_fetch_one_cell("select battleBelong from cfg_things where tid=$medalTypeId");
	if (! lockUser ( $uid ))
	throw new Exception ( $GLOBALS ['pacifyPeople'] ['server_busy'] );

	if(($goods ['group'] == 7)&&($battleBelong!=9001))
	{
		// fix bug 1606, change the sequence of checking exploit and level--chenxi, 2009.11.25
		$userAreaLevel = getUserBattleNetArea($uid);
		if($userAreaLevel < $goods ['creditPrice'])
		{
			throw new Exception ( $GLOBALS['buyGoods']['no_enough_Level'] );
		}

		$userExploitTotal = getBattleNetExploit($uid);

		if($userExploitTotal < $medalNeed)
		{
			throw new Exception ( $GLOBALS['buyGoods']['no_enough_Exploit'] );
		}
	}
	else if($battleBelong==9001){
		$param=array();
		$param[]=$creditNeed;
		$userPVELevel = getUserPVENetLevel($uid);
		if($userPVELevel < $goods ['creditPrice'])
		{
			throw new Exception ( $GLOBALS['buyGoods']['no_enough_achievement_Level'] );
		}
		$userGongxun = getBattleNetPVEGongxun($uid,$param);
		if($userGongxun < $medalNeed)
		{
			throw new Exception ( $GLOBALS['buyGoods']['no_enough_gongxun'] );
		}
	}else {
		if($isCBArmor){
			$chibigongxun=getChibiGongXun($uid);
			$chibigongxun=array_shift($chibigongxun);
			if ($chibigongxun< $creditNeed) {
				throw new Exception ($GLOBALS['buyGoods']['no_enough_chibi_gongxun']);
			}
		} else {
			$userInfo = sql_fetch_one ( "select honour, nobility from sys_user where uid='$uid'" );
			//用户有的荣誉
			$userCredit = $userInfo ['honour'];
			if ($userCredit < $creditNeed) {
				throw new Exception ( $GLOBALS ['buyGoods'] ['no_enough_Credit'] );
			}
		}
		$medalinfo = sql_fetch_one ( "select count from sys_things where uid='$uid' and tid='$medalTypeId'" );
		$userMedal = 0;
		if (empty ( $medalinfo )) {
		} else
		$userMedal = $medalinfo ['count'];
		if ($userMedal < $medalNeed) {
			$mName = sql_fetch_one_cell ( "select name from cfg_things where tid=$medalTypeId" );
			$msg = sprintf ( $GLOBALS ['buyGoods'] ['no_enough_Medal'], $mName );
			throw new Exception ( $msg );
		}

	}

	//限制商品
	if (($id == 121) && ($userInfo ['nobility'] < 1))
	throw new Exception ( $GLOBALS ['buyGoods'] ['nobility_limit'] );
	if ($goods ['totalCount'] < 2000000000) {
		if ($goods ['totalCount'] == 0)
		throw new Exception ( $GLOBALS ['buyGoods'] ['sold_out'] );
	}
	//if (($goods ['userbuycnt'] > 0) || ($goods ['battledaybuycnt'] > 0)) //属于限制商品
	if ($goods ['userbuycnt'] > 0) //属于限制商品,取消战场物品每日限制
	{
		$buycnt = intval ( sql_fetch_one_cell ( "select `count` from log_shop_buy_cnt where uid='$uid' and `sid`='$id'" ) );
		if (($goods ['userbuycnt'] > 0) && ($buycnt + $cnt > $goods ['userbuycnt'])) {
			if ($goods ['userbuycnt'] > $buycnt) {
				$remain = $goods ['userbuycnt'] - $buycnt;
				$msg = sprintf ( $GLOBALS ['buyGoods'] ['reach_remain_amountLimit'], $goods ['userbuycnt'], $buycnt, $remain );
				throw new Exception ( $msg );
			} else {
				$msg = sprintf ( $GLOBALS ['buyGoods'] ['reach_buy_limit'], $goods ['userbuycnt'] );
				throw new Exception ( $msg );
			}
		}
//		$todaybuycnt = intval ( sql_fetch_one_cell ( "select sum(count) from log_shop where uid ='$uid' and shopid = '$id' and time>=unix_timestamp(curdate())" ) );
//		if (($goods ['battledaybuycnt'] > 0) && ($todaybuycnt + $cnt > $goods ['battledaybuycnt'])) {
//			if ($goods ['battledaybuycnt'] > $todaybuycnt) {
//				$remain = $goods ['battledaybuycnt'] - $todaybuycnt;
//				$msg = sprintf ( $GLOBALS ['buyGoods'] ['reach_remain_amount_todayLimit'], $goods ['battledaybuycnt'], $todaybuycnt, $remain );
//				throw new Exception ( $msg );
//			} else {
//				$msg = sprintf ( $GLOBALS ['buyGoods'] ['reach_buy_todayLimit'], $goods ['battledaybuycnt'] );
//				throw new Exception ( $msg );
//			}
//		}
	}
	//一手交货 普通物品, 战场装备, 战场将领
	if ($battleGoodsType == 0) //普通物品
	addGoods ( $uid, $goods ['gid'], $goods ['pack'] * $cnt, 2 );
	else if ($battleGoodsType == 1) //属于战场装备
	{
		if ($cnt > 1)
		throw new Exception ( $GLOBALS ['buyGoods'] ['only_one_goods'] );
		addBattleArmor ( $uid, $goods ['gid'], $cnt ); //gid这里其实对应 cfg_armor的arm id
	} else if ($battleGoodsType == 2) //属于战场将领
	{
		if ($cnt > 1)
		throw new Exception ( $GLOBALS ['buyGoods'] ['only_one_goods'] );
		addBattleHero ( $uid, $goods ['gid'], $cnt, $cityId ); //gid这里其实对应 cfg_hero的hero id
	}
	sql_query ( "insert into log_shop_buy_cnt (`uid`,`sid`,`count`) values ('$uid','$id','$cnt') on duplicate key update `count`=`count`+'$cnt'" );

	if(($battleBelong==9001)){
		$param=array();
		$param[]=9001;
		$param[]=$medalNeed;
		if(deductUserPVEGongxun($uid,$param)<0){
			throw new Exception ( $GLOBALS['buyGoods']['unsuccessful'] );
		}
		
	}else if($goods ['group'] != 7)
	{
		//一手交勋章
		if($isCBArmor){
			addGongunAndMedal($uid, - $creditNeed, - $medalNeed, $medalTypeId);
		}else{
			addCreditAndMedal ( $uid, - $creditNeed, - $medalNeed, $medalTypeId );
		}
	}
	else {
		// fix bug 1621 -- chenxi, 2009.12.1
		if(subtractBattleNetExpoit($uid,$medalNeed) < 0)
		throw new Exception ( $GLOBALS['buyGoods']['unsuccessful'] );
	}
	unlockUser ( $uid );
	$ret = array ();
	$ret [] = $userCredit = $creditNeed;
	return $ret;
}

function getUserPVENetLevel($uid) {
	$achivement_id=sql_fetch_one_cell("select max(achivement_id) from sys_user_achivement where uid='$uid' and achivement_id between 70022 and 70024");
	if ($achivement_id==70024) {
		$level=4;
	}else if ($achivement_id==70023) {
		$level=3;
	}else if($achivement_id==70022){
		$level=2;
	}else {
		$level=1;
	}
	return $level;
}

function medalChange($uid, $param) {
	$count = array_shift ( $param );
	$medalTypeId = array_shift ( $param );
	/**
	 * 20平定黄巾勋章=1汉室勋章。
		10袁军官渡勋章=1汉室勋章。
		12曹军官渡勋章=1汉室勋章。
	 */
	$mName = "";
	$radio = 1;
	
	$nameAndType = sql_fetch_one ( "select a.name,b.type from cfg_things a, cfg_battle_field b where a.tid=$medalTypeId  and a.battleBelong = b.id" );
	$mName = $nameAndType ['name'];
	if ($medalTypeId == 30003) {
		$radio = 12;
	} else {
		if ($nameAndType ['type'] == 0) {
			$radio = 20;
		} else {
			$radio = 10;
		}
	}
	$medalinfo = sql_fetch_one ( "select * from sys_things where tid='$medalTypeId' and uid='$uid'" );
	if (empty ( $medalinfo )) {
		$emsg = sprintf ( $GLOBALS ['buyGoods'] ['no_medal'], $mName, $count );
		throw new Exception ( $emsg );
	}
	if (intval ( $medalinfo ['count'] ) < $radio * intval ( $count )) {
		$msg = sprintf ( $GLOBALS ['buyGoods'] ['no_medal'], $mName, $count );
		throw new Exception ( $msg );
	}
	$needMedal = $radio * intval ( $count );
	
	$hsMedal = sql_fetch_one ( "select * from sys_things where uid='$uid' and tid='30000'" );
	
	if (empty ( $hsMedal )) {
		sql_query ( "insert into sys_things(`uid`, `tid`, `count`) values('$uid', '30000' , '$count')" ); //更新汉室勋章的数量
	} else {
		sql_query ( "update sys_things set count=count+'$count' where uid='$uid' and tid='30000'" ); //更新汉室勋章的数量
	}
	sql_query ( "update sys_things set count=count-'$needMedal' where uid='$uid' and tid='$medalTypeId'" );
	
	$ret = array ();
	return $ret;
}

function getMedalExchangeInfo($uid, $param) {
	$ret = array ();
	
	$xunZhangInfos = sql_fetch_rows ( "select T.tid,T.name,T.type,ifnull(sys_things.count,0) as `count` from (select a.tid,a.name, b.type from cfg_things a, cfg_battle_field b where a.tid >30000 and a.tid <30500 and a.battlebelong !=0 and a.battleBelong = b.id and b.type !=3)T left join sys_things on sys_things.uid = $uid and T.tid = sys_things.tid" );
	foreach ( $xunZhangInfos as $xunZhang ) {
		//$retCell = array(0,0,0,0);
		$retCell = array ();
		$retCell [] = $xunZhang ["name"];
		$retCell [] = $xunZhang ["tid"];
		if ($xunZhang ["tid"] == 30003) {
			$retCell [] = 12;
		} else {
			if ($xunZhang ["type"] == 0) {
				$retCell [] = 20;
			} else {
				$retCell [] = 10;
			}
		}
		$retCell [] = $xunZhang ["count"];
		
		$ret [] = $retCell;
	}
	return $ret;
}

function getMedalRecord($uid, $param) {
	//30000~30003 tid: 汉室勋章	平定黄巾勋章	袁军官渡勋章	曹军官渡勋章
	return sql_fetch_rows ( "select * from sys_things as A, cfg_things as B where A.uid='$uid' and A.tid = B.tid and A.tid>=30000 and A.tid<=30010" );
}

function resetTodayWarCount($uid, $param) {
	return useQingZhanShuDirect ( $uid, 138 );
}
function getPVPBattleInfo($uid, $param) {
	$bid = intval ( array_shift ( $param ) );
	$ret = array ();
	$now = sql_fetch_one_cell ( "select unix_timestamp()" );
	
	//现有开启等级
	$openSql = implode ( ",",$GLOBALS['battle']['open_level'] );
	
	$allbattles = sql_fetch_rows ( "select u.id,u.bid,c.name,u.level,u.maxpeople,u.minpeople,u.state  from sys_user_battle_field u left join cfg_battle_field c on u.bid = c.id  where u.bid = $bid and u.level in ($openSql) order by u.level desc,u.id" );
	$battles = array ();
	foreach ( $allbattles as $battle ) {
		$hasPush = false;
		foreach ( $battles as $item ) {
			if ($item ["level"] == $battle ["level"]) {
				$hasPush = true;
				break;
			}
		}
		if ($hasPush)
			continue;
		$battlefieldid = $battle ['id'];
		$people = sql_fetch_one_cell ( "select count(*) from sys_user_battle_state where state=0 and battlefieldid=$battlefieldid" );
		$battle ['people'] = $people;
		$battles [] = $battle;
	}
	$ret [] = $battles;
	if ($bid == 2001) {
		$ret [] = sql_fetch_rows ( "select unionid,name from cfg_battle_union where bid = $bid " );
	} else if ($bid == 4001) {
		//如果是讨伐董卓，玩家只能选择加入汉室
		$ret [] = sql_fetch_rows ( "select unionid,name from cfg_battle_union where bid = $bid and unionid=7" );
	} else if ($bid == 6001) {
		//逐鹿中原
		$ret [] = sql_fetch_rows ( "select 25 as unionid,name from sys_user where uid=$uid" );
	} else if ($bid == 7001) {
		$ret [] = sql_fetch_rows ( "select unionid,name from cfg_battle_union where bid = $bid and unionid in (26,28)" );
	}
	return $ret;
}

function deleteOldBattleTasks($uid) {
	//添加新任务之前删除原有战场任务
	$tasks = sql_fetch_one_cell ( "SELECT GROUP_CONCAT(DISTINCT tid) FROM cfg_task_goal WHERE sort in (100,110,120)" );

	if ($tasks != "") {
		sql_query ( "DELETE FROM sys_user_task where uid='$uid' and tid in ($tasks)" );
		$taskgoals = sql_fetch_one_cell ( " select group_concat(id) from cfg_task_goal where tid in ($tasks)" );
		sql_query ( "DELETE FROM sys_user_goal where uid='$uid' and gid in ($taskgoals)" );
	}
}

function addUserTasks($uid, $param) {
	$tasks = array_shift ( $param );
	foreach ( $tasks as $task ) {
		sql_query ( "insert into sys_user_task (uid,tid,state) values ($uid,'$task',0) on duplicate key update state=0" );
	}
}
/**
 * 创建一个剧情战场，仅限type=1的情况。
 *
 * @param unknown_type $uid
 * @param unknown_type $param
 * @return unknown
 */
function createBattle($uid, $param) {
	
	$nobility = intval ( sql_fetch_one_cell ( "select nobility from sys_user  where uid = $uid" ) );
	//推恩
	$nobility = getBufferNobility ( $uid, $nobility );
	if ($nobility < 1)
		throw new Exception ( $GLOBALS ['battle'] ['nobility_not_rearch'] );
	
	$bid = intval ( array_shift ( $param ) );
	//throw new Exception($bid);
	$level = intval ( array_shift ( $param ) );
	if (sql_check ( "select * from sys_user_battle_state where state=0 and uid='$uid'" )) {
		throw new Exception ( $GLOBALS ['battle'] ['user_already_in_battle'] );
	}
	
	$battlecfg = sql_fetch_one ( "select * from cfg_battle_field where id='$bid' and state<>0" );
	if (empty ( $battlecfg )) {
		throw new Exception ( $GLOBALS ['battle'] ['no_battle_field'] );
	}
	
	$battlecount = sql_fetch_one_cell ( "select count(*) from sys_user_battle_field where bid='$bid' and state=0" );
	if ($battlecount >= $battlecfg ['maxcount']) {
		throw new Exception ( $GLOBALS ['battle'] ['too_many_battle'] );
	}
	
	$todayWarCont = sql_fetch_one_cell ( "select today_war_count from mem_user_schedule where uid = $uid" );
	if (empty ( $todayWarCont ))
		$todayWarCont = 0;
	if ($todayWarCont >= $battlecfg ['maxdaycount']) {
		throw new Exception ( $GLOBALS ['battle'] ['today_war_count_reach_limit'] );
	}
	if(defined("BATTLE_NET_ENABLE") && BATTLE_NET_ENABLE){
//		$battlenetinfo = sendRemoteRequest ( $uid, "getUserBattleState" );
//		if ($battlenetinfo [0] == 2) {
//			throw new Exception ( $GLOBALS['battle']['already_in_battlenet'] );
//		}
		if(sql_check("select 1 from sys_battlenet_waiting_queue where uid=$uid")){
			throw new Exception ( $GLOBALS['battle']['already_in_battlenet'] );
		}
	}
	$currentuser = sql_fetch_one ( "select honour,name from sys_user where uid='$uid'  " );
	////$currenthonour=0;
	

	$currenthonour = $currentuser ['honour'];
	$username = $currentuser ['name'];
	if ($currenthonour < 0) {
		throw new Exception ( $GLOBALS ['battle'] ['honour_invalid'] );
	}
	//等级检查
	if ($level > $battlecfg ['maxlevel']) {
		$level = $battlecfg ['maxlevel'];
	}
	if ($level < 1) {
		$level = 1;
	}
	/*//现有开启战场等级
	$isOpenLevel=false;
	$openLevel=$GLOBALS['battle']['open_level'];
	for($i=0;$i<count($openLevel);$i++){
		if($openLevel[$i]==$level){
			$isOpenLevel=true;
			break;
		}
	}
	if(!$isOpenLevel) throw new Exception($GLOBALS['battle']['not_open_level']);
	*/
	$needTaoFa = 0;
	if ($level > 10) {
		$needTaoFa = $level - 10;
		//11级以上 要有足够的讨伐令
		if (! checkGoodsCount ( $uid, 137, $needTaoFa )) {
			throw new Exception ( "not_enough_goods137#$needTaoFa" );
		}
	
	}
	checkUserQualityForJoinBattle ( $uid, $bid );
	
	$cfgbattlecitys = 0;
	//生成战场信息		
	$now = time ();
	$endtime = $now + $battlecfg ['maxtime'];
	$result = sql_query ( "insert into sys_user_battle_field (bid,createuid,level,maxpeople,state,starttime,endtime,type) values('$bid','$uid','$level','$battlecfg[maxpeople]',-1,$now,$endtime,'$battlecfg[type]')" );
	if ($result) {
		$battlefieldid = sql_fetch_one_cell ( "select LAST_INSERT_ID()" );
		//写入sys_battle_city
		$currentbattlecityinfo = array ();
		
		$cfgbattlecitys = sql_fetch_rows ( "select * from cfg_battle_city where bid='$bid'" );
		foreach ( $cfgbattlecitys as $cfgbattlecity ) {
			
			$cid = battleid2cid ( $battlefieldid, $cfgbattlecity ['xy'] );
			//生成城市
			sql_query ( "insert into sys_battle_city (cid,battlefieldid,nextxy,name,uid,unionid,`drop`,rate,xy,image,winpoint,losepoint,reinforce_soldiers,can_quest) values($cid,$battlefieldid,'$cfgbattlecity[nextxy]','$cfgbattlecity[name]',0, '$cfgbattlecity[unionid]', '$cfgbattlecity[drop]', '$cfgbattlecity[rate]','$cfgbattlecity[position]','$cfgbattlecity[image]','$cfgbattlecity[winpoint]','$cfgbattlecity[losepoint]','$cfgbattlecity[reinforce_soldiers]','$cfgbattlecity[can_quest]')" );
			$cfgtroops = sql_fetch_rows ( "select * from cfg_battle_troop where bid='$bid' and xy='$cfgbattlecity[xy]' and type=0 " );
			//$cfghero = sql_fetch_one ("select * from cfg_battle_hero where hid='$cfgheroid' ");
			//将领对部队的加成只需要通过$cfghero读取
			//sql_query("insert into sys_city_hero (uid,name,npcid,sex,face,state,command_base,affairs_base,bravary_base,wisdom_base,level) values (0,'$cfghero[name]',0,'$cfghero[sex]','$cfghero[face]',10,'$cfghero[name]','$cfghero[command_base]','$cfghero[bravary_base]','$cfghero[wisdom_base]','$cfghero[level]' ) ");
			

			//生成驻守的部队
			foreach ( $cfgtroops as $cfgtroop ) {
				$soldiers = createSoldier ( $cfgtroop ['npcvalue'], $cfgtroop ['soldiers'], $level );
				sql_query ( "insert into sys_troops (cid,uid,hid,soldiers,state,`drop`,rate,battlefieldid,battleunionid,bid)  values  ($cid,0,'$cfgtroop[hid]','$soldiers',4,'$cfgtroop[drop]','$cfgtroop[rate]',$battlefieldid,'$cfgtroop[unionid]',$bid) " );
			
			}
			if ($cfgbattlecity ['unionid'] == "-1") {
				
				//$cfgbattlecity['unionid']=resetBattleFieldUid($cid);
				$unionlog = resetBattleFieldUid ( $cid );
				$cfgbattlecity ['unionid'] = $unionlog ["unionid"];
			}
			
			$battlecityinfo = array ("image" => $cfgbattlecity ['image'], "cid" => $cid, "battlefieldid" => $battlefieldid, "nextxy" => $cfgbattlecity ["nextxy"], "name" => $cfgbattlecity ["name"], "uid" => 0, "unionid" => $cfgbattlecity ['unionid'], "drop" => $cfgbattlecity ['drop'], "rate" => $cfgbattlecity ['rate'], "flag" => $cfgbattlecity ['flag'], "flagchar" => $cfgbattlecity ['flagchar'], "xy" => $cfgbattlecity ['position'] );
			$currentbattlecityinfo [] = $battlecityinfo;
			
		//读取以前此类战场的记录
		

		//更新用户的战场状态，插入当前战场荣誉
		

		}
		$unionid = $battlecfg ['user_unionid'];
		$unioninfo = sql_fetch_one ( "select * from cfg_battle_union where unionid=$unionid" );
		$startcid = battleid2cid ( $battlefieldid, $battlecfg ['startcid'] );
		sql_query ( "insert into sys_user_battle_state (uid,bid,battlefieldid,unionid,level) values($uid,$bid,$battlefieldid,$unionid,$level) on duplicate key update  battlefieldid=$battlefieldid,bid=$bid,unionid=$unionid,level=$level" );
		if ($needTaoFa > 0) {
			reduceGoods ( $uid, 137, $needTaoFa );
		}
		deleteOldBattleTasks ( $uid );
		if ($bid == 1001) {
			//黄巾之乱，添加任务
			$tasks = sql_fetch_rows ( "select id from cfg_task where `group` in (60000,60001,60002,60003,60004) and pretid=-1" );
			foreach ( $tasks as $task ) {
				sql_query ( "insert into sys_user_task (uid,tid,state) values ($uid,'$task[id]',0) on duplicate key update state=0" );
			}
		}
		else {
			//添加初始任务
			$tasks = explode ( ",", $unioninfo ["init_tasks"] );
			foreach ( $tasks as $task ) {
				sql_query ( "insert into sys_user_task (uid,tid,state) values ($uid,'$task',0) on duplicate key update state=0" );
			}
			//十常侍之乱，添加任务
		//$tasks=sql_fetch_rows("select id from cfg_task where `group` in (60019,60020) and pretid=-1" );
		//foreach($tasks as $task){
		//	sql_query("insert into sys_user_task (uid,tid,state) values ($uid,'$task[id]',0) on duplicate key update state=0");
		//}
		}
		$fieldname = $unioninfo ['name'];
		playerAttendBattle ( $battlefieldid, $unionid, $uid, $username, $fieldname );
		$currentbattleinfo = array ("battlefieldid" => $battlefieldid, "bid" => $bid, "createuid" => $uid, "level" => $level, "maxpeople" => $battlecfg ['maxpeople'], "endtime" => $endtime, "honour" => $currenthonour, "name" => $battlecfg ['name'], "type" => $battlecfg ['type'], "startcid" => $battlecfg ['startcid'], "state" => 0, "unionid" => $unionid );
		$currentbattlecityinfo = setFlags ( $uid, $unionid, $currentbattlecityinfo );
		$ret = array ();
		$ret [] = $currentbattleinfo;
		$ret [] = $currentbattlecityinfo;
		$ret [] = getBattleStartCityInfo ( $bid, $unionid );
		
		sql_query ( "insert into mem_user_schedule (`uid`,`today_war_count`) values ('$uid',1) on duplicate key update `today_war_count`=today_war_count+1" );
		
		$id = sql_fetch_one_cell ( "select id from cfg_task_goal where  tid=282" );
		if(!empty($id))
		  sql_query ( "insert into sys_user_goal(`uid`,`gid`) values ('$uid','$id') on duplicate key update gid='$id'" );
		sql_query("update sys_user_battle_field set state = 0 where id = $battlefieldid");
		//触发初始化事件
		sendBattleEvent ( 0, $uid, $bid, $battlefieldid, $unionid, 0, 9 );
		return $ret;
		//
	}
	unlockUser ( $uid );
	throw new Exception ( $GLOBALS ['battle'] ['create_failed'] );
	//返回当前战场的状态
}

/**
 * 取得某个据点当前的军队
 *
 * @param unknown_type $uid
 * @param unknown_type $param
 * @return unknown
 */
function getBattleFieldState($uid, $param) {
	$ret = array ();
	$battleinfo = firstGetUserBattleInfo ( $uid );
	$battlefieldid = intval ( array_shift ( $param ) );
	$unionid = intval ( array_shift ( $param ) );
	$cid = intval ( array_shift ( $param ) );
	$cityname = array_shift ( $param );
	$cityname = addslashes($cityname);
	$currentTroopCid = intval ( array_shift ( $param ) );
	
	$troopcityinfo = sql_fetch_one ( "select * from sys_battle_city where cid='$currentTroopCid'" );
	if ($battleinfo ["bid"] == 6001 && $cid != $currentTroopCid) {
		if (empty ( $troopcityinfo ) || ! canGoto ( $cid, $troopcityinfo ['nextxy'] )) {
			throw new Exception ( $GLOBALS ['battle'] ['can_not_view_city'] );
		}
	}
	$curcityinfo = sql_fetch_one ( "select * from sys_battle_city where cid='$cid'" );
	$troops1 = sql_fetch_rows ( "select s.*,CASE WHEN u.name IS NULL THEN u2.name ELSE u.name END AS `name`, CASE WHEN u.name IS NULL THEN u2.name ELSE u.name END AS `union`,h.name as hero,h.can_chat as can_chat,h.can_chat as enable_chat,h.no_attack as no_attack,h.level as level from sys_troops s left join cfg_battle_union u on s.battleunionid=u.unionid left join cfg_battle_hero h on (s.hid=h.hid ) left join sys_user u2 on s.battleunionid=u2.uid where s.uid<897  and s.battlefieldid='$battlefieldid' and s.cid='$cid' and (s.state=4 or s.state=3)" );
	$troops2 = sql_fetch_rows ( "select s.*,u2.name as name,CASE WHEN u.name IS NULL THEN u2.name ELSE u.name END AS `union`,h.name as hero,0 as can_chat,0 as enable_chat,0 as no_attack,h.level as level from sys_troops s left join cfg_battle_union u on s.battleunionid=u.unionid left join sys_city_hero h on (s.hid=h.hid ) left join sys_user u2 on s.uid=u2.uid where s.uid>897  and s.battlefieldid='$battlefieldid' and s.cid='$cid' and ((s.state=4 or s.state=3) or s.targetcid=$cid)" );
	$ret [] = $cityname;
	$ret [] = $cid;
	$troops3 = array_merge ( $troops1, $troops2 );
	if ($battleinfo ["bid"] == 7001) {
		//三让徐州不能一直交谈
		foreach ( $troops3 as &$troopitem ) {
			if ($troopitem ["can_chat"]) {
				if (! enoughIntervalFromLastMark ( $battlefieldid, 2, $troopitem ["hid"] )) {
					$troopitem ["enable_chat"] = "0";
				}
			}
		}
	}
	
	$ret [] = $troops3;
	
	$cityinfo = sql_fetch_rows ( "select * from sys_battle_city where battlefieldid='$battlefieldid' " );
	$cityinfo = setFlags ( $uid, $unionid, $cityinfo );
	$ret [] = $cityinfo;
	$ret [] = sql_fetch_rows ( "select t.state,t.targetcid,t.soldiers,t.cid,t.id,t.ulimit,h.face,h.sex,h.name as heroname,h.level,h.hid,h.herotype from sys_troops t left join sys_city_hero h on t.hid=h.hid where t.uid='$uid' and t.battlefieldid='$battlefieldid' order by t.id asc" );
	
	//可否从改城池增兵
	$ret [] = sql_check ( "SELECT 1 FROM sys_battle_city a,sys_troops b WHERE a.battlefieldid=$battlefieldid AND a.cid=$cid AND a.ownerunionid=$uid AND a.cid=b.cid AND b.uid=a.uid AND b.uid=$uid" );
	//可否探索
	$ret [] = (($battleinfo ["unionid"] == 28 || $battleinfo ["unionid"] == 30) && $curcityinfo ["can_quest"] == "1");
	$interval = 300;
	if ($battleinfo ["unionid"] == 28)
		$interval = 300;
	else if ($battleinfo ["unionid"] == 30)
		$interval = 60;
	$ret [] = (($battleinfo ["unionid"] == 28 || $battleinfo ["unionid"] == 30) && $curcityinfo ["can_quest"] == "1" && enoughIntervalFromLastMark ( $battlefieldid, 1, $cid, false, $interval ));
	return $ret;

}

//从空白将领出点击出征
function getCurrentCityGroundLevel1($uid, $param) {
	$cid = intval ( array_shift ( $param ) );
	$groundLevel = intval ( sql_fetch_one_cell ( "select level from sys_building where cid=$cid and bid='" . ID_BUILDING_GROUND . "'" ) );
	if (empty ( $groundLevel ) || $groundLevel == 0) {
		throw new Exception ( $GLOBALS ['getGroundInfo'] ['no_ground_built'] );
	}
	$ret = array ();
	$ret [] = $groundLevel;
	return $ret;
}
//从
function getCurrentCityGroundLevel2($uid, $param) {
	return getCurrentCityGroundLevel1 ( $uid, $param );
}

/**
 * 军队从城池出发前往某个战场
 *
 * @param unknown_type $uid
 * @param unknown_type $param
 * @return unknown
 */
function startBattleTroop($uid, $param) {
	$cid = intval(array_shift ( $param ));
	$hid = intval(array_shift ( $param ));
	$soldiers = array_shift ( $param );
	$soldiers = addslashes($soldiers);
	$usegoods = array_shift ( $param );
	
	$xy = intval(array_shift ( $param ));
	
	if ($hid <= 0) {
		throw new Exception ( $GLOBALS ['start_battle_troop'] ['no_hero'] );
	}
	//检查目标战场的有效性
	$battleinfo = firstGetUserBattleInfo ( $uid );
	
	//据点战场，要等人数够了开启才行
	if ($battleinfo [bid] != 6001 && $battleinfo ['type'] == 1 && $battleinfo ['state'] == 2) {
		throw new Exception ( $GLOBALS ['battle'] ['battle_in_ready'] );
	}
	if ($battleinfo ['type'] == 1) {
		//据点战场不能使用军旗
		$usegoods = 0;
	}
	$count_limit = 0;
	if ($battleinfo [bid] == 6001) {
		$startcityinfo = sql_fetch_one ( "select 0 as needhonour from sys_user_battle_state where state=0 and uid=$uid and startcid=$xy limit 1" );
	} else {
		$startcityinfo = sql_fetch_one ( "select * from cfg_battle_start_city where bid='$battleinfo[bid]' and xy='$xy' and unionid='$battleinfo[unionid]' " );
	}
	if (empty ( $startcityinfo )) {
		throw new Exception ( $GLOBALS ['start_battle_troop'] ['city_not_allow'] );
	}
	if ($startcityinfo ['needhonour'] > $battleinfo ['honour']) {
		throw new Exception ( $GLOBALS ['start_battle_troop'] ['not_enought_honour'] );
	}
	$alreadySentTroopCount = sql_fetch_one_cell ( "select sent_troop_count from sys_user_battle_state where state=0 and uid=$uid" );
	if (($battleinfo [bid] == 5001 && $alreadySentTroopCount == 1) || ($battleinfo [bid] == 6001 && $alreadySentTroopCount == 2)) {
		throw new Exception ( $GLOBALS ['start_battle_troop'] ['reach_limit'] );
	}
	
	$targetcid = battleid2cid ( $battleinfo ['battlefieldid'], $xy );
	
	$troopcount = sql_fetch_one_cell ( "select count(*) from sys_troops where uid='$uid' and battlefieldid='$battleinfo[battlefieldid]' " );
	$maxTroopCount = 2;
	if ($battleinfo ["bid"] == "5001")
		$maxTroopCount = 1;
	if ($troopcount >= $maxTroopCount) {
		throw new Exception ( $GLOBALS ['start_battle_troop'] ['max_troop'] );
	}
	
	//检查是否有军旗，军旗的id是59
	if (($usegoods) && ! checkGoods ( $uid, 59 ))
		throw new Exception ( "not_enough_goods59" );
		//当前城池已经发生战斗了，就不能出发了。
	/*if (sql_check("select * from mem_world where wid='".cid2wid($cid)."' and state='1'")){
		throw new Exception($GLOBALS['StartTroop']['city_in_battle']);
		}*/
	
	//检查一下是暗渡陈仓状态，如果没有暗渡陈仓，中了十面埋伏的话就不能出兵了
	$anduTime = sql_fetch_one_cell ( "select `endtime` from mem_city_buffer where cid='$cid' and buftype=5 and `endtime`>unix_timestamp()" );
	if (empty ( $anduTime )) {
		//检查一下十面埋伏状态
		$shimianTime = sql_fetch_one_cell ( "select `endtime`-unix_timestamp() from mem_city_buffer where cid='$cid' and buftype=8 and `endtime`>unix_timestamp()" );
		if (! empty ( $shimianTime )) {
			$msg = sprintf ( $GLOBALS ['StartTroop'] ['suffer_ShiMianMaiFu'], MakeTimeLeft ( $shimianTime ) );
			throw new Exception ( $msg );
		}
	}
	
	//校场的等级不够的话就不能出发
	$groundLevel = intval ( sql_fetch_one_cell ( "select level from sys_building where cid=$cid and bid='" . ID_BUILDING_GROUND . "'" ) );
	
	$troopCount = intval ( sql_fetch_one_cell ( "select count(*) from sys_troops where   uid='$uid' and cid=$cid " ) );
	if ($troopCount >= $groundLevel) {
		throw new Exception ( $GLOBALS ['StartTroop'] ['insufficient_ground_level'] );
	}
	
	/////////////// need test
	//$taskname = array($GLOBALS['StartTroop']['transport'],$GLOBALS['StartTroop']['send'],$GLOBALS['StartTroop']['detect'],$GLOBALS['StartTroop']['harry'],$GLOBALS['StartTroop']['occupy']);
	$forceNeed = 5;
	//检查一下英雄的有效性。
	if ($hid != 0) {
		$heroInfo = sql_fetch_one ( "select * from sys_city_hero h left join mem_hero_blood m on m.hid=h.hid where h.hid='$hid' and h.uid='$uid' and h.cid='$cid'" );
		if (empty ( $heroInfo )) {
			throw new Exception ( $GLOBALS ['StartTroop'] ['hero_not_found'] );
		} else if ($heroInfo ['state'] != 0 || $heroInfo ['hero_health'] != 0) {
			throw new Exception ( $GLOBALS ['StartTroop'] ['hero_is_busy'] );
		} else {
			$force = $heroInfo ['force'];
			if ($force < $forceNeed) {
				throw new Exception ( sprintf ( $GLOBALS ['StartTroop'] ['hero_not_enough_force'], $GLOBALS ['StartTroop'] ['goto_battle'], $forceNeed ) );
			}
		}
	}
	
	//if ($battleinfo ["bid"] == 5001) {
		//如果是千里走单骑触发，出征的将领需要使用关羽武魂
		// 增加判断条件：关羽本人也可以出征
		//if ($hid != 870 && ! sql_check ( "SELECT * FROM mem_hero_buffer a,cfg_goods b WHERE a.hid=$hid AND a.buftype+25=b.gid AND b.gid%10000=870 and b.gid between 100000 and 200000 AND a.endtime>UNIX_TIMESTAMP()" )) {
		//	throw new Exception ( $GLOBALS ['StartTroop'] ['must_with_guanyu_wuhun'] );
		//}
	//}
	
	//要检查自己是不是刚使用过高级迁城令
	$lastMoveCD = intval ( sql_fetch_one_cell ( "select last_adv_move+43200-unix_timestamp() from mem_city_schedule where cid='$cid'" ) );
	if ($lastMoveCD > 0) {
		$msg = sprintf ( $GLOBALS ['StartTroop'] ['adv_move_cooldown'], MakeTimeLeft ( $lastMoveCD ) );
		throw new Exception ( $msg );
	}
	
	//检查一下当前城池是否有这么多军队
	$citySoldiers = sql_fetch_map ( "select * from sys_city_soldier where cid='$cid'", "sid" );
	
	$soldierArray = explode ( ",", $soldiers );
	$numSoldiers = array_shift ( $soldierArray );
	$takeSoldiers = array (); //真正带出去的军队
	$soldierAllCount = 0;
	$cihouCount = 0;
	if($numSoldiers>12){
		throw new Exception($GLOBALS['StartTroop']['too_many_sid']);
	}
	if($battleinfo['bid']==5001){
		if($numSoldiers>11){
			throw new Exception($GLOBALS['StartTroop']['too_many_sid11']);
		}
	}
	for($i = 0; $i < $numSoldiers; $i ++) {
		$sid = array_shift ( $soldierArray );
		$cnt = array_shift ( $soldierArray );
		if ($cnt < 0)
			$cnt = 0;
		$takeSoldiers [$sid] = $cnt;
		//实际军队人数<客户端传来的人数
		if ($citySoldiers [$sid] ['count'] < $cnt) {
			throw new Exception ( $GLOBALS ['StartTroop'] ['no_so_many_army'] );
		}
		$soldierAllCount += $cnt;
		if ($sid == 3)
			$cihouCount += $cnt;
	}
	
	if ($soldierAllCount <= 0)
		throw new Exception ( $GLOBALS ['StartTroop'] ['no_soldier'] );
		
	//不能斥候独立出征
	if (($cihouCount >= $soldierAllCount))
		throw new Exception ( $GLOBALS ['battle'] ['spy_cant_alone'] );
		
	//出征人数限制
	$groundLevelLimit = $groundLevel * 10000 * GAME_SPEED_RATE;
	$limitadd = 0;
	//使用军旗
	if (! empty ( $usegoods )) {
		$limitadd += 25;
	}
	$myCityInfo = sql_fetch_one ( "select * from sys_city where cid='$cid'" );
	//名城出征人数
	if ($myCityInfo ['type'] > 0) {
		if ($myCityInfo ['type'] == 1)
			$limitadd += 25;
		else if ($myCityInfo ['type'] == 2)
			$limitadd += 50;
		else if ($myCityInfo ['type'] == 3)
			$limitadd += 75;
		else if ($myCityInfo ['type'] == 4)
			$limitadd += 100;
	}
	if ($limitadd > 0) {
		$groundLevelLimit = ceil ( $groundLevelLimit * (100 + $limitadd) / 100 );
	}
	
	//针对战场等级的出征人数限制
	

	$groundLevelLimit1 = $GLOBALS ['battle'] ['soldier_limit'] [$battleinfo ["level"]];
	if (! empty ( $usegoods )) {
		$groundLevelLimit1 = ceil ( $groundLevelLimit1 * 1.25 );
	}
	$groundLevelLimit = min ( $groundLevelLimit, $groundLevelLimit1 );
	// 千里走单骑空出两个辎重车
	if($battleinfo ["bid"] == 5001)
		$groundLevelLimit -= 2;
	//是否超过人数
	if ($soldierAllCount > $groundLevelLimit) {
		throw new Exception ( sprintf ( $GLOBALS ['StartTroop'] ['no_enough_ground_level'], $groundLevelLimit ) );
	}
	
	//////////////////TODO  军队速度的计算，是否应该放在最后
	

	//行军技巧和驾驭技巧
	

	//步兵速度加成
	$speedAddRate1 = 1 + intval ( sql_fetch_one_cell ( "select level from sys_city_technic where cid='$cid' and tid=12" ) ) * 0.1;
	//骑兵速度加成
	$speedAddRate2 = 1 + intval ( sql_fetch_one_cell ( "select level from sys_city_technic where cid='$cid' and tid=13" ) ) * 0.05;
	//将领速度加成
	$speedAddRate3 = 1;
	if ($hid != 0) {
		$speedAddRate3 = 1 + $heroInfo ['speed_add_on'] * 0.01;
	}
	
	//单程时间 ＝ 每格子距离/最慢兵种速度+宿营时间（每格距离＝60000/game_speed_rate）
	$pathLength = 162000;
	$minSpeed = 999999999;
	// TODO 可以优化和缓存
	$soldierConfig = sql_fetch_rows ( "select * from cfg_soldier where fromcity=1 order by sid", "sid" );
	foreach ( $soldierConfig as $soldier ) //找到当前军队里最慢的
{
		if ($hid > 0) {
			//计算将领身上因为装备而得到的该士兵的速度加成
			$sid = $soldier->sid;
			$speedAdd = 0;
			if (($sid >= 1 && $sid <= 12)||($sid >=45 && $sid <=50)) {
				$attid = 2000 + ($sid - 1) * 100 + 11; //取得属性id
				$attr = sql_fetch_one ( "select * from sys_hero_attribute where hid=$hid and attid=$attid" );
				if (! empty ( $attr )) {
					$speedAdd = $attr ['value'];
				}
			}
		}
		if (! empty ( $takeSoldiers [$soldier->sid] )) {
			//除了斥候外的步兵速度加成
			if ($soldier->sid < 7 && $soldier->sid != 3) {
				$minSpeed = min ( $soldier->speed * $speedAddRate1 * $speedAddRate3 + $speedAdd, $minSpeed );
			} //骑兵加成
			else {
				$minSpeed = min ( $soldier->speed * $speedAddRate2 * $speedAddRate3 + $speedAdd, $minSpeed );
			}
		}
	}
	
	$pathNeedTime = $pathLength / $minSpeed; //需要多少时间
	

	$pathNeedTime = intval ( floor ( $pathNeedTime ) );
	
	//出征耗粮 ＝ 兵的耗粮/小时*12*单程时间   
	$foodUse = 0;
	$allpeople = 0;
	
	foreach ( $soldierConfig as $soldier ) //找到当前军队里最慢的
{
		if (! empty ( $takeSoldiers [$soldier->sid] )) {
			$foodUse += $soldier->food_use * $takeSoldiers [$soldier->sid];
			
			$allpeople += $soldier->people_need * $takeSoldiers [$soldier->sid];
		}
	}
	
	$now = sql_fetch_one_cell ( "select unix_timestamp()" );
	$foodRate = ceil ( ($battleinfo ['endtime'] - $now) / 3600 );
	if ($foodRate <= 0)
		$foodRate = 1;
		//    if ($task == 4) $foodRate = 5;    //现在不用了
	$foodUse *= $foodRate;
	//军队行程耗粮量
	//检查一下当前城池是否有足够的军粮，直接吃掉
	$food = sql_fetch_one_cell ( "select food from mem_city_resource where cid='$cid'" );
	if ($food < $foodUse)
		throw new Exception ( $GLOBALS ['StartTroop'] ['no_enough_food'] );
		
	//  throw new Exception($food.":".$foodUse);
	

	if ($hid != 0) //让将领置成出征状态
{
		sql_query ( "update sys_city_hero set state=2 where hid='$hid'" );
		sql_query ( "update mem_hero_blood set `force`=GREATEST(0,`force`-$forceNeed) where hid='$hid'" );
	}
	//减资源
	addCityResources ( $cid, 0, 0, 0, - $foodUse, 0, 0 );
	//减兵员
	addCitySoldiers ( $cid, $takeSoldiers, false );
	//减军旗
	if ($usegoods) {
		reduceGoods ( $uid, 59, 1 );
		completeTaskWithTaskid ( $uid, 303 );
	}
	if ($battleinfo [bid] == 6001) {
		//逐鹿中原移动时间固定为60秒
		$pathNeedTime = 60;
	}
	$troopid = sql_insert ( "insert into sys_troops (`uid`,`cid`,`hid`,`task`,`state`,`starttime`,`pathtime`,`endtime`,`soldiers`,`resource`,`people`,`fooduse`,`battlefieldid`,`battleunionid`,`targetcid`,`startcid`,bid,ulimit) values ('$uid','$cid','$hid',7,'0',unix_timestamp(),'$pathNeedTime',unix_timestamp()+10,'$soldiers','0','$allpeople','$foodUse','$battleinfo[battlefieldid]','$battleinfo[unionid]','$targetcid','$cid','$battleinfo[bid]',$soldierAllCount)" );
	/*
	 * 平台接口
	 */
	if (defined ( "PASSTYPE" )) {
		try {
			require_once 'game/agents/AgentServiceFactory.php';
			AgentServiceFactory::getInstance ( $uid )->addPushArmyOperationTimeEvent ( $troopid );
		} catch ( Exception $e ) {
			try {
				file_put_contents ( "./agents/log/interface-error.log", date ( "Y-m-d H:i:s", time () ) . " || " . $e->getMessage () . "\n", FILE_APPEND );
			} catch ( Exception $err ) {
			
			}
		}
	}
	
	//设置当前军队的战术为玩家当前战述
	$tactics = sql_fetch_one ( "select * from sys_user_tactics where uid='$uid'" );
	if ($tactics) {
		sql_query ( "replace into sys_troop_tactics (`troopid`,`plunder`,`invade`,`patrol`,`field`) values ('$troopid','$tactics[plunder]','$tactics[invade]','$tactics[patrol]','$tactics[field]')" );
	}
	
	sql_query ( "update sys_user_battle_state set sent_troop_count=sent_troop_count+1 where state=0 and uid=$uid" );
	$ret = array ();
	$ret [] = $GLOBALS ['StartTroop'] ['succ'];
	return $ret;
}

function getCityBattleTroopDetail($uid, $param) {
	$troopid = intval ( array_shift ( $param ) );
	$troop = sql_fetch_one ( "select id,cid,uid,soldiers,hid,targetcid,battleunionid from sys_troops where id='$troopid'" );
	if (empty ( $troop ))
		throw new Exception ( $GLOBALS ['callBackTroop'] ['invalid_army'] );
	$info = array ();
	$info ['id'] = $troop ['id'];
	$info ['uid'] = $troop ['uid'];
	$info ['soldiers'] = $troop ['soldiers'];
	$info ['unionname'] = sql_fetch_one_cell ( "select name from cfg_battle_union where unionid='$troop[battleunionid]'" );
	if ($troop ['uid'] <= 897) {
		//君主的
		$info ['name'] = $info ['unionname'];
		if ($troop [battleunionid] > 1000) {
			$info ['name'] = sql_fetch_one_cell ( "select name from sys_user where uid='$troop[battleunionid]' " );
			$info ['unionname'] = $info ['name'];
		}
		$info ['heroinfo'] = sql_fetch_one ( "select name,level from cfg_battle_hero where hid=$troop[hid]" );
	} else {
		$info ['name'] = sql_fetch_one_cell ( "select name from sys_user where uid='$troop[uid]' " );
		if ($troop [battleunionid] > 1000) {
			$info ['unionname'] = $info ['name'];
		}
		$info ['heroinfo'] = sql_fetch_one ( "select name,level from sys_city_hero where hid=$troop[hid]" );
	}
	
	$buffers = sql_fetch_rows ( "select buftype,bufparam from mem_troops_buffer where troopid='$troopid' " );
	
	if (! empty ( $buffers )) {
		$info ['buffers'] = $buffers;
	}
	
	$ret = array ();
	$ret [] = $info;
	return $ret;
}

/**
 * 读取一只军队的详细信息
 *
 * @param unknown_type $uid
 * @param unknown_type $param
 * @return unknown
 */
function getBattleTroopDetail($uid, $param) {
	$troopid = intval ( array_shift ( $param ) );
	
	$troop = sql_fetch_one ( "select id,cid,uid,soldiers,hid,targetcid,battleunionid,pathtime,endtime,state from sys_troops where id='$troopid'" );
	
	if (empty ( $troop ))
		throw new Exception ( $GLOBALS ['callBackTroop'] ['invalid_army'] );
	$info = array ();
	
	$info ['id'] = $troop ['id'];
	$info ['uid'] = $troop ['uid'];
	$info ['soldiers'] = $troop ['soldiers'];
	$info ['pathtime'] = $troop ['pathtime'];
	$info ['endtime'] = $troop ['endtime'];
	$info ['state'] = $troop ['state'];
	
	$info ['unionname'] = sql_fetch_one_cell ( "select name from cfg_battle_union where unionid='$troop[battleunionid]'" );
	
	if ($troop ['uid'] <= 897) {
		//君主的
		$info ['name'] = $info ['unionname'];
		if ($troop [battleunionid] > 1000) {
			$info ['name'] = sql_fetch_one_cell ( "select name from sys_user where uid='$troop[battleunionid]' " );
			$info ['unionname'] = $info ['name'];
		}
		$info ['heroinfo'] = sql_fetch_one ( "select name,level from cfg_battle_hero where hid=$troop[hid]" );
	} else {
		$info ['name'] = sql_fetch_one_cell ( "select name from sys_user where uid='$troop[uid]' " );
		if ($troop [battleunionid] > 1000) {
			$info ['unionname'] = $info ['name'];
		}
		
		$info ['heroinfo'] = sql_fetch_one ( "select name,level from sys_city_hero where hid=$troop[hid]" );
	}
	
	//前往派遣或者前往攻击
	if ($troop ['state'] == 0 || $troop ['state'] == 1 || $troop ['state'] == 2 || $troop ['state'] == 3) {
		$info ['targetcityname'] = sql_fetch_one_cell ( "select name from sys_battle_city where cid='$troop[targetcid]' " );
	} else if ($troop ['state'] == 4) {
		$info ['targetcityname'] = sql_fetch_one_cell ( "select name from sys_battle_city where cid='$troop[cid]'" );
	} else {
		$info ['targetcityname'] = "--";
	}
	
	if ($troop ['state'] == 0) {
		$info ['state'] = $GLOBALS ['battle'] ['state_0'];
	} else if ($troop ['state'] == 1) {
		$info ['state'] = $GLOBALS ['battle'] ['state_1'];
	} else if ($troop ['state'] == 2) {
		$info ['state'] = $GLOBALS ['battle'] ['state_2'];
		$info ['pathtime'] = "--";
		$info ['endtime'] = "--";
	} else if ($troop ['state'] == 3) {
		$info ['state'] = $GLOBALS ['battle'] ['state_3'];
		$info ['pathtime'] = "--";
		$info ['endtime'] = "--";
	} else if ($troop ['state'] == 4) {
		$info ['state'] = $GLOBALS ['battle'] ['state_4'];
		$info ['pathtime'] = "--";
		$info ['endtime'] = "--";
	}
	
	$buffers = sql_fetch_rows ( "select buftype,bufparam from mem_troops_buffer where troopid='$troopid' " );
	
	if (! empty ( $buffers )) {
		$info ['buffers'] = $buffers;
	}
	
	//检查军队的道具
	$troopthing = sql_fetch_one ( "SELECT b.name as name,a.count as count FROM sys_troop_things a,cfg_things b WHERE a.thing_id=b.tid AND troop_id=$troopid LIMIT 1" );
	if (! empty ( $troopthing )) {
		$info ['thingname'] = $troopthing ["name"];
		$info ['thingcount'] = $troopthing ["count"];
	} else {
		$info ['thingname'] = "--";
		$info ['thingcount'] = "--";
	}
	$ret = array ();
	$ret [] = $info;
	return $ret;
}

/**
 * 军队撤离战场
 *
 * @param unknown_type $uid
 * @param unknown_type $param
 * @return unknown
 */
function callBackArmy($uid, $param) {
	$troopid = intval ( array_shift ( $param ) );
	$troop = sql_fetch_one ( "select * from sys_troops where id='$troopid' and uid='$uid'" );
	if (empty ( $troop )) {
		throw new Exception ( $GLOBALS ['battle'] ['troop_not_exist'] );
	}
	$troopstate = $troop ['state'];
	if ($troopstate != 4) {
		throw new Exception ( $GLOBALS ['battle'] ['troop_not_stay'] );
	}
	
	//援军返还
	$SOLDIER_PEOPLE = array (0, 1, 1, 1, 1, 1, 2, 3, 6, 4, 5, 10, 8 );
	//返还援军
	$reinforce = sql_fetch_one_cell ( "select group_concat(sid,',',count) as soldiers from sys_battle_reinforce where troopid='$troop[id]' " );
	//返还的战场荣誉
	$backhonour = 0;
	//返还后部队是否还有士兵
	$emptytroop = true;
	//返还后的士兵
	$newsoldiers = "";
	//返还后的士兵兵种数目
	$newsoldierstypecount = 0;
	if (! empty ( $reinforce )) {
		//剩下的兵种map
		$orisoldiersmap = getSoldierMap ( $troop ['soldiers'] );
		//原来的士兵
		//$orisoldiers=$troop['soldiers'];
		//援兵的兵种
		$reinforcearray = explode ( ",", $reinforce );
		$reinforcearraycount=count($reinforcearray)/2;
		//计算每个兵种的返还，如果返还后小于0，则视为0。
		for($i=0;$i<$reinforcearraycount;$i++){
			$sid=array_shift($reinforcearray);
			//调遣过的援军数目
			$reinforcemapcount=array_shift($reinforcearray);
			//现在剩下的数目
			$oricount=$orisoldiersmap[$sid];
			$newcount=0;
			if(!empty($oricount)){
				$newcount=$oricount-$reinforcemapcount;
				if($newcount<0){
					$newcount=0;
				}
				//该兵种返还的数值
				$backcount=$oricount-$newcount;
				$backhonour+=floor($backcount*$SOLDIER_PEOPLE[$sid]/100);
			}
			$orisoldiersmap[$sid]=$newcount;
		}
		if(!$emptytroop){
			$newsoldiers=$newsoldierstypecount.$newsoldiers;
		}
		$newsoldiers=getSoldierString($orisoldiersmap);
	}else{
		$emptytroop=false;
		$newsoldiers=$troop['soldiers'];
	}
	//$newsoldiers=getSoldierString($orisoldiersmap);
	if($newsoldiers!=""){
		//所有军队设置为返回状态
		$result=sql_query("update sys_troops set starttime=unix_timestamp(),task=7,state=1,soldiers='$newsoldiers',endtime=unix_timestamp()+30,battlefieldid=0,cid=startcid where id='$troop[id]'");
		if($result){
			if($backhonour>0){
				sql_query("update sys_user set honour=honour+$backhonour where uid='$uid'");
				$msg = sprintf($GLOBALS['battle']['callback_army_honour'], $backhonour);
				//发战报
				sendReport($uid,0,40,0,0,$msg);
			}
		}
	}else{
		//返还援军后军队为0，则直接把将领扔回城。
		$result=sql_query("update sys_city_hero set state=0 where hid='$troop[hid]' ");
		//$result=
		if($result){
			//删除军队
			sql_query("delete from sys_troops where id='$troop[id]'");
			if($backhonour>0){
				sql_query("update sys_user set honour=honour+$backhonour where uid='$uid'");
				$msg = sprintf($GLOBALS['battle']['callback_army_honour'], $backhonour);
				//发战报
				sendReport($uid,0,40,0,0,$msg);
			}
		}
	}
	//$result=sql_query("update sys_troops set task=7,state=1,endtime=unix_timestamp()+pathtime,battlefieldid=0,cid=startcid where id='$troopid'");
	$ret=array();
	if($result){
		$ret[]=$GLOBALS['battle']['callback_succ'];
		resetBattleFieldUid($troop['cid']);
		if($troop['bid']==4001){
			//如果从董卓战场撤军，且这只军队身上有玉玺的话，刷掉玉玺，其他玩家可以继续打玉玺
			if(sql_check("select count from sys_troop_things where troop_id=$troopid and thing_id=45")){
				sql_query("delete from sys_troop_things where troop_id=$troopid and thing_id=45");
				sql_query("delete from sys_things where uid=$uid and tid=45");
				sql_query("delete from sys_user_battle_event where eventid=(select id from cfg_battle_event where bid=4001 and targettype=21 and targetid1=45 )");
				sql_query("update sys_user_battle_state set unionid=7 where state=0 and uid=$uid");
				$heroname=$heroinfo = sql_fetch_one_cell("select name from sys_city_hero where hid='$troop[hid]'");
				$msg=sprintf($GLOBALS['battle']['callback_yuxi_army'],$heroname);
				sendBattleMsg($troop['battlefieldid'],$troop['battleunionid'],$msg);
			}
		}
		if($troop['bid']==5001){
			//从千里走电器副本撤军就意味着副本直接失败
			$newparam=array();
			$newparam[]=sql_fetch_one_cell("select name from sys_user where uid=$uid");
			quitBattle($uid,$newparam); 
		}
	}
	else
	$ret[]=$GLOBALS['battle']['callback_fail'];
	return $ret;
}

function callBackToField($uid, $param) {
	$troopid = intval ( array_shift ( $param ) );
	$battlefieldId=intval(array_shift($param));
	if($battlefieldId){//如果是赤壁里面的军队就掉用赤壁里面的调回方法
		if($battlefieldId==11000){
			return sendChibiRemoteRequest($uid,"callBackChiBiTroop",$troopid);
		}
	}
	$troop = sql_fetch_one ( "select * from sys_troops where id='$troopid' and uid='$uid'" );
	$troopstate = $troop ['state'];
	if (empty ( $troop )) {
		throw new Exception ( $GLOBALS ['battle'] ['troop_not_exist'] );
	}
	
	if ($troopstate != 0) {
		throw new Exception ( $GLOBALS ['battle'] ['troop_not_ahead'] );
	}
	
	if ($troop ["cid"] == $troop ["startcid"]) {
		//援军返还
		$SOLDIER_PEOPLE = array (0, 1, 1, 1, 1, 1, 2, 3, 6, 4, 5, 10, 8 );
		//返还援军
		$reinforce = sql_fetch_one_cell ( "select group_concat(sid,',',count) as soldiers from sys_battle_reinforce where troopid='$troop[id]' " );
		//返还的战场荣誉
		$backhonour = 0;
		//返还后部队是否还有士兵
		$emptytroop = true;
		//返还后的士兵
		$newsoldiers = "";
		//返还后的士兵兵种数目
		$newsoldierstypecount = 0;
		if (! empty ( $reinforce )) {
			//剩下的兵种map
			$orisoldiersmap = getSoldierMap ( $troop ['soldiers'] );
			//原来的士兵
			//$orisoldiers=$troop['soldiers'];
			//援兵的兵种
			$reinforcearray = explode ( ",", $reinforce );
			
			$reinforcearraycount = count ( $reinforcearray ) / 2;
			
			//计算每个兵种的返还，如果返还后小于0，则视为0。
			for($i = 0; $i < $reinforcearraycount; $i ++) {
				$sid = array_shift ( $reinforcearray );
				//调遣过的援军数目
				$reinforcemapcount = array_shift ( $reinforcearray );
				//现在剩下的数目
				$oricount = $orisoldiersmap [$sid];
				$newcount = 0;
				if (! empty ( $oricount )) {
					$newcount = $oricount - $reinforcemapcount;
					if ($newcount < 0) {
						$newcount = 0;
					}
					//该兵种返还的数值
					$backcount = $oricount - $newcount;
					$backhonour += floor ( $backcount * $SOLDIER_PEOPLE [$sid] / 100 );
				}
				$orisoldiersmap [$sid] = $newcount;
			}
			if (! $emptytroop) {
				$newsoldiers = $newsoldierstypecount . $newsoldiers;
			}
			$newsoldiers = getSoldierString ( $orisoldiersmap );
		
		} else {
			$emptytroop = false;
			$newsoldiers = $troop ['soldiers'];
		}
		//$newsoldiers=getSoldierString($orisoldiersmap);
		

		if ($newsoldiers != "") {
		
		}
		//返还荣誉
		if ($backhonour > 0) {
			sql_query ( "update sys_user set honour=honour+$backhonour where uid='$uid'" );
		}
		
		$result = sql_query ( "update sys_troops set state=1,soldiers='$newsoldiers',endtime=unix_timestamp() - starttime + unix_timestamp(),starttime=unix_timestamp() where id='$troopid'" );
	} else {
		$result = sql_query ( "update sys_troops set state=1,endtime=unix_timestamp() - starttime + unix_timestamp(),starttime=unix_timestamp() where id='$troopid'" );
	}
	
	$ret = array ();
	if ($result)
		$ret [] = $GLOBALS ['battle'] ['callback_succ'];
	else
		$ret [] = $GLOBALS ['battle'] ['callback_fail'];
	return $ret;
}

/**
 * 战场派遣
 *
 * @param unknown_type $uid
 * @param unknown_type $param
 * @return unknown
 */
function battleTroopDispatch($uid, $param) {
	$troopid = intval ( array_shift ( $param ) );
	$targetcid = intval ( array_shift ( $param ) );
	$heroname = array_shift ( $param );
	$heroname=addslashes($heroname);
	$targetName = array_shift ( $param );
	$targetName = addslashes($targetName);
	$battlefieldinfo = ifFrozeExit ( $uid );
	$troop = sql_fetch_one ( "select * from sys_troops where id='$troopid' and uid='$uid' and battlefieldid='$battlefieldinfo[battlefieldid]' " );
	if (empty ( $troop )) {
		throw new Exception ( $GLOBALS ['battle'] ['troop_not_exist'] );
	}
	if ($troop ['state'] != 4) {
		throw new Exception ( $GLOBALS ['battle'] ['troop_not_stay'] );
	}
	
	//检查目的据点
	$targetcity = sql_fetch_one ( "select * from sys_battle_city where cid=$targetcid" );
	if (empty ( $targetcity )) {
		throw new Exception ( $GLOBALS ['battle'] ['city_not_exist'] );
	}
	
	//检查出发据点
	$origincity = sql_fetch_one ( "select * from sys_battle_city where cid='$troop[cid]'" );
	if (empty ( $origincity )) {
		throw new Exception ( $GLOBALS ['battle'] ['city_not_exist'] );
	}
	
	//是否是我方阵营
	/*$unionid=$troop['battleunionid'];
	 if($targetcity['unionid']!=$unionid&&$targetcity['unionid']>0){
		throw new Exception($GLOBALS['battle']['not_same_union']);
		}*/
	
	//是否连通
	if (! canGoto ( $targetcid, $origincity ['nextxy'] )) {
		throw new Exception ( $GLOBALS ['battle'] ['city_cannot_goto'] );
	}
	//如果是董卓副本，身上有玉玺的话需要检查是否停留的够久
	if ($troop ['battlefieldid'] == 4001) {
		
		$yuxi = sql_fetch_one ( "select * from sys_troop_things where troop_id=$troopid and thing_id=45" );
		if (! empty ( $yuxi )) {
			$timediff = sql_fetch_one_cell ( "select unix_timestamp()-arrive_time from sys_troops where id=$troopid" );
			if (intval ( $timediff ) < 60) {
				throw new Exception ( $GLOBALS ['battle'] ['must_stay_one_minute'] );
			}
		}
	}
	
	$cid = $targetcid % 1000;
	$battlefieldid = $battlefieldinfo ['battlefieldid'];
	$attack_lock = sql_fetch_one ( "SELECT * FROM mem_battle_attack_lock WHERE battlefieldid='$battlefieldid' AND cid='$cid' AND (uid in ('$uid',0) or uid IS NULL)" );
	if (! empty ( $attack_lock )) {
		throw new Exception ( $attack_lock ['msg'] );
	}
	
	$result = sql_query ( "update sys_troops set starttime=unix_timestamp(),state=0,targetcid='$targetcid',task=8,endtime=unix_timestamp()+pathtime,arrive_time=0 where id='$troopid' " );
	if ($result) {
		$ret [] = $GLOBALS ['StartTroop'] ['succ'];
		//$fieldname=$GLOBALS['battle']['union_name'][$battlefieldinfo['unionid']];
		//playTroopLeave($battlefieldinfo['battlefieldid'], $battlefieldinfo['unionid'], $fieldname, $targetName,$heroname)	;
		resetBattleFieldUid ( $troop ['cid'] );
	} else
		$ret [] = $GLOBALS ['StartTroop'] ['fail'];
	return $ret;

}

//点击攻击按钮
function getInfoForBattleArmyAttack($uid, $param) {
	$troopid = intval ( array_shift ( $param ) );
	$targettroopid = intval ( array_shift ( $param ) );
	$targetname = array_shift ( $param );
	$targetname = addslashes($targetname);
	$troop = sql_fetch_one ( "select t.cid,t.id,t.uid,t.soldiers,t.pathtime,t.state,h.name as heroname,h.level,h.hid from sys_troops t left join sys_city_hero h on t.hid=h.hid where t.id='$troopid' and t.uid='$uid'" );
	if (empty ( $troop ))
		throw new Exception ( $GLOBALS ['battle'] ['troop_not_exist'] );
	if ($troop ['state'] != 4) {
		throw new Exception ( $GLOBALS ['battle'] ['troop_not_stay'] );
	}
	
	$targettroop = sql_fetch_one ( "select cid,id,uid,soldiers,hid,state from sys_troops where id='$targettroopid'" );
	if (empty ( $targettroop ))
		throw new Exception ( $GLOBALS ['battle'] ['troop_not_exist'] );
	if ($troop ["cid"] == $targettroop ["cid"]) {
		//同据点攻击,30秒后开始 对手不能在攻击状
		if ($targettroop ['state'] != 4) {
			throw new Exception ( $GLOBALS ['battle'] ['troop_in_same_city_not_stay'] );
		}
		$troop ["pathtime"] = 10;
	} else {
		$targettroop ['soldiers'] = "";
	}
	
	if ($targettroop ["uid"] > 0) {
		//被攻击的部队是玩家部队
		$heroinfo = sql_fetch_one ( "select name,level from sys_city_hero where hid='$targettroop[hid]'" );
		$targettroop ['heroname'] = $heroinfo ['name'];
		$targettroop ['level'] = $heroinfo ['level'];
	} else {
		//防守方的部队
		//被攻击的部队是玩家部队
		$heroinfo = sql_fetch_one ( "select name,level from cfg_battle_hero where hid='$targettroop[hid]'" );
		$targettroop ['heroname'] = $heroinfo ['name'];
		$targettroop ['level'] = $heroinfo ['level'];
	}
	
	$ret [] = $troop;
	$ret [] = $targettroop;
	$ret [] = $targetname;
	
	return $ret;

}

function getInfoForBattleArmyReinforce($uid, $param) {
	$cid = intval ( array_shift ( $param ) );
	$troopinfo = sql_fetch_rows ( "select t.cid,t.id,t.uid,t.soldiers,t.pathtime,t.state,h.name as heroname,h.level,h.hid from sys_troops t left join sys_city_hero h on t.hid=h.hid where t.cid='$cid' and t.uid='$uid'" );
	$reinforce_soldiers = sql_fetch_one_cell ( "select reinforce_soldiers from sys_battle_city where cid=$cid" );
	$ret [] = $troopinfo;
	$ret [] = $reinforce_soldiers;
	return $ret;
}

function addArmyFromReinforce($uid, $param) {
	$cid = intval ( array_shift ( $param ) );
	$troopid = intval ( array_shift ( $param ) );
	$sid = intval ( array_shift ( $param ) );
	//检查此城池中现有的补充兵员数量
	$reinforce_soldiers = sql_fetch_one_cell ( "select reinforce_soldiers from sys_battle_city where cid=$cid" );
	$troopinfo = sql_fetch_one ( "select soldiers,ulimit from sys_troops where id=$troopid" );
	$oriSoldiers = $troopinfo ["soldiers"];
	$upLimit = intval ( $troopinfo ["ulimit"] );
	if (empty ( $oriSoldiers )) {
		throw new Exception ( $GLOBALS ['battle'] ['troop_not_exist'] );
	}
	$soldiersMap = getSoldierMap ( $reinforce_soldiers );
	$oriSoldiersMap = getSoldierMap ( $oriSoldiers );
	$curTotalCount = 0;
	foreach ( $oriSoldiersMap as $tsid => $tcount ) {
		$curTotalCount += $tcount;
	}
	
	if (! empty ( $soldiersMap [$sid] )) {
		if (empty ( $upLimit ) || $upLimit == 0) {
			$availCnt = $soldiersMap [$sid];
		} else {
			$availCnt = max ( 0, min ( $soldiersMap [$sid], $upLimit - $curTotalCount ) );
		}
		
		if ($availCnt > 0) {
			enhanceArmy ( $troopid, $oriSoldiers, $sid, $availCnt, $troopinfo ["startcid"], $troopinfo ["hid"] );
			$soldiersMap [$sid] -= $availCnt;
			$newSoldierString = getSoldierString ( $soldiersMap );
			sql_query ( "update sys_battle_city set reinforce_soldiers='$newSoldierString' where cid=$cid" );
		} else {
			throw new Exception ( $GLOBALS ['battle'] ['soldier_uplimit'] );
		}
	} else {
		throw new Exception ( $GLOBALS ['battle'] ['no_reinforce'] );
	}
	$ret = array ();
	$ret [] = $GLOBALS ['battle'] ['add_army_succ'];
	return $ret;
}
//点击攻击按钮
function battlePatrol($uid, $param) {
	$troopid = intval ( array_shift ( $param ) );
	$targettroopid = intval ( array_shift ( $param ) );
	
	//先检查有没有信鸽
	if (! checkGoods ( $uid, 140, 1 )) {
		//throw new Exception($GLOBALS['battle']['not_enought_gezi']);
		throw new Exception ( "not_enough_goods140" );
	
	}
	
	$troop = sql_fetch_one ( "select t.cid,t.id,t.uid,t.soldiers,t.pathtime,t.state,h.name as heroname,h.level from sys_troops t left join sys_city_hero h on t.hid=h.hid where t.id='$troopid' and t.uid='$uid'" );
	if (empty ( $troop )) {
		unlockuser ( $uid );
		throw new Exception ( $GLOBALS ['battle'] ['troop_not_exist'] );
	}
	$targettroop = sql_fetch_one ( "select cid,id,uid,soldiers,hid,bid,state from sys_troops where id='$targettroopid'" );
	if (empty ( $targettroop )) {
		unlockuser ( $uid );
		throw new Exception ( $GLOBALS ['battle'] ['troop_not_exist'] );
	}
	if ($targettroop ["uid"] > 0) {
		//被攻击的部队是玩家部队
		$heroinfo = sql_fetch_one ( "select name,level from sys_city_hero where hid='$targettroop[hid]'" );
		$targettroop ['heroname'] = $heroinfo ['name'];
		$targettroop ['level'] = $heroinfo ['level'];
	} else {
		//防守方的部队
		//被攻击的部队是玩家部队
		$heroinfo = sql_fetch_one ( "select name,level from cfg_battle_hero where hid='$targettroop[hid]'" );
		$targettroop ['heroname'] = $heroinfo ['name'];
		$targettroop ['level'] = $heroinfo ['level'];
	}
	
	$xy = $targettroop ['cid'] % 1000;
	$targetcityname = sql_fetch_one_cell ( "select name from cfg_battle_city where bid='$targettroop[bid]' and xy=$xy " );
	
	$msg = sprintf ( $GLOBALS ['battle'] ['patrol_report'], $targetcityname, $targettroop ['heroname'], $targettroop ['level'] );
	$soldiers = $targettroop ['soldiers'];
	
	$soldiersarray = explode ( ",", $soldiers );
	$soldierscount = array_shift ( $soldiersarray );
	for($i = 0; $i < $soldierscount; $i ++) {
		$sid = array_shift ( $soldiersarray );
		$count = array_shift ( $soldiersarray );
		$msg .= $GLOBALS ['battle'] ['patrol_report_soldier'] [$sid] . " " . $count . "<br/>";
	}
	
	//发战报
	sendReport ( $uid, 0, 45, 0, 0, $msg );
	
	reduceGoods ( $uid, 140, 1 );
	
	throw new Exception ( $GLOBALS ['battle'] ['patrol_report_suc'] );
	//return $ret;
}

//计算目标是否连通
function canGoto($targetcid, $nextcids) {
	if(defined("TEST") && TEST===true){
		return true;
	}
	$targetxy = $targetcid % 1000;
	$nextcidarray = explode ( ",", $nextcids );
	$nextcount = array_shift ( $nextcidarray );
	$cangoto = false;
	for($i = 0; $i < $nextcount; $i ++) {
		$temp = array_shift ( $nextcidarray );
		if ($temp == $targetxy) {
			$cangoto = true;
			break;
		} else if ($temp == (0 - $targetxy)) {
			throw new Exception ( $GLOBALS ['battle'] ['road_not_opens'] );
		}
	}
	return $cangoto;
}

//选择一只军队，只更新他的当前位置
function selectOneTroop($uid, $param) {
	$troopid = intval ( array_shift ( $param ) );
	$troop = sql_fetch_one ( "select id,cid from sys_troops where id= '$troopid' and uid='$uid'" );
	if (empty ( $troop )) {
		throw new Exception ( $GLOBALS ['battle'] ['troop_not_exist'] );
	}
	$ret [] = array ();
	$ret [] = $troop;
	return $ret;
}

function getInfoForBattleArmySend($uid, $param) {
	$troopid = intval ( array_shift ( $param ) );
	$targetid = intval ( array_shift ( $param ) );
	$targetname = array_shift ( $param );
	$targetname = addslashes($targetname);
	$troop = sql_fetch_one ( "select t.id,t.uid,t.soldiers,t.pathtime,t.state,h.name as heroname,h.level,t.cid from sys_troops t left join sys_city_hero h on t.hid=h.hid where t.id='$troopid' and t.uid='$uid'" );
	if (empty ( $troop ))
		throw new Exception ( $GLOBALS ['battle'] ['troop_not_exist'] );
	
	if ($troop ['state'] != 4) {
		throw new Exception ( $GLOBALS ['battle'] ['troop_not_stay'] );
	}
	if ($troop ['cid'] == $targetid) {
		throw new Exception ( $GLOBALS ['battle'] ['troop_in_same_city'] );
	}
	$troop ['targetname'] = $targetname;
	$troop ['targetid'] = $targetid;
	
	$ret [] = $troop;
	return $ret;
}

function getQuitResult($uid, $param) {
	$battleinfo = firstGetUserBattleInfo ( $uid );
	
	$ret = array ();
	if ($battleinfo ['state'] == 2) {
		$ret [] = 2;
	} else if ($battleinfo ['winner'] == $battleinfo ['unionid']) {
		$ret [] = 1;
	} else if ($battleinfo ['winner'] == - 1) {
		$ret [] = - 1;
	} else {
		$ret [] = 0;
	}
	return $ret;
}
/**
 * 中途退出战场，遣返援军，重新计算战场荣誉。
 *
 * @param unknown_type $uid
 * @param unknown_type $param
 * @return unknown
 */
function quitBattle($uid, $param) {
	$nick = sql_fetch_one_cell ( "select name from sys_user where uid=$uid" );
	$battleinfo = firstGetUserBattleInfo ( $uid );
	/*
	 * 战场倍率活动
	 */
	$nowtimestamp = $battleinfo ["starttime"];
	if(empty($nowtimestamp))
		$nowtimestamp = sql_fetch_one_cell ( "select unix_timestamp()" );
	$baseRate = 1;
	$act = sql_fetch_one ( "select * from cfg_act_battle where state>0 and (((weekday(from_unixtime($nowtimestamp))+1)=actid and hour(from_unixtime($nowtimestamp)) between starthour and endhour-1) || (date>0 and $nowtimestamp between date+starthour*3600 and date+endhour*3600)) limit 1" );
	if (! empty ( $act )) {
		if ($act ["state"] == 1) {
			$baseRate = $act ["rate"];
		} else if ($act ["state"] == 2 && sql_fetch_one_cell ( "select count(*) from cfg_act_battle_details where actid=$act[actid] and bid=$battleinfo[bid]" )) {
			$baseRate = $act ["rate"];
		}
	}
	
	//清理该用户的数据	
	$troops = sql_fetch_rows ( "select * from sys_troops where uid='$uid' and battlefieldid='$battleinfo[battlefieldid]' " );
	foreach ( $troops as $troop ) {
		if ($troop ['state'] != 4) {//4驻军
			throw new Exception ( $GLOBALS ['battle'] ['troop_in_fight_when_quit'] );
		}
	}
	$level = $battleinfo ["level"];
	//退出后的战场荣誉
	$orihonour = $battleinfo ['honour'];
	//援军返还荣誉
	$newhonour = 0;
	
	$SOLDIER_PEOPLE = array (0, 1, 1, 1, 1, 1, 2, 3, 6, 4, 5, 10, 8 );
	foreach ( $troops as $troop ) {
		//返还援军
		$reinforceDatas = sql_fetch_rows ( "select type,group_concat(sid,',',count) as soldiers from sys_battle_reinforce where troopid='$troop[id]' group by type " );
		//返还的战场荣誉
		$backhonour = 0;
		//返还后部队是否还有士兵
		$emptytroop = true;
		//返还后的士兵
		$newsoldiers = "";
		
		//剩下的兵种map
		$orisoldiersmap = getSoldierMap ( $troop ['soldiers'] );
		foreach ( $reinforceDatas as $reinforceData ) {
			$reinforce = $reinforceData ["soldiers"];
			
			//援兵的兵种
			$reinforcearray = explode ( ",", $reinforce );
			
			$reinforcearraycount = count ( $reinforcearray ) / 2;
			
			//计算每个兵种的返还，如果返还后小于0，则视为0。
			for($i = 0; $i < $reinforcearraycount; $i ++) {
				$sid = array_shift ( $reinforcearray );
				//调遣过的援军数目
				$reinforcemapcount = array_shift ( $reinforcearray );
				//现在剩下的数目
				$oricount = $orisoldiersmap [$sid];
				$newcount = 0;
				if (! empty ( $oricount )) {
					$newcount = $oricount - $reinforcemapcount;
					if ($newcount < 0) {
						$newcount = 0;
					}
					//该兵种返还的数值
					$backcount = $oricount - $newcount;
					if ($reinforceData ["type"] == 0) {
						$backhonour += floor ( $backcount * $SOLDIER_PEOPLE [$sid] / 100 );
					}
				}
				$orisoldiersmap [$sid] = $newcount;
			}
		}
		$newsoldiers = getSoldierString ( $orisoldiersmap );
		
		$newhonour += $backhonour;
		if ($newsoldiers != "") {
			//所有军队设置为返回状态
			$result = sql_query ( "update sys_troops set starttime=unix_timestamp(),task=7,state=1,soldiers='$newsoldiers',endtime=unix_timestamp()+pathtime,battlefieldid=0,cid=startcid where id='$troop[id]'" );
		} else {
			//返还援军后军队为0，则直接把将领扔回城。
			sql_query ( "delete from sys_troops  where id='$troop[id]' " );
			sql_query ( "update sys_city_hero set state=0 where hid='$troop[hid]' " );
		}
		resetBattleFieldUid ( $troop ['cid'] );
	}
	
	$result = $battleinfo ['winner'];
	$quittype = 0;
	//胜利，失败，或者逃跑扣除的荣誉
	

	$winhonour = 0;
	
	$canwin = true;
	//黄巾 要检查刷子
	if ($battleinfo ['unionid'] == 1) {
		//看是不是胜利推出过
		if (! sql_check ( "select * from log_battle_honour where uid='$uid' and battlefieldid='$battleinfo[battlefieldid]' and result=0 " ))
			$canwin = true;
		if ($canwin) {
			//如果没有胜利退出过，要参与完成主线任务，否则不能胜利		
			$goalsid = sql_fetch_one_cell ( "select group_concat(id) from cfg_task_goal where tid in (60000,60001,60002) " );
			if (sql_fetch_one_cell ( "select count(*) from sys_user_goal where uid=$uid and gid in ($goalsid) " ) != 3) {
				//三个主线任务都完成
				$canwin = false;
			} else //检查并更新黄巾史诗任务开启状态
{
				//	    		$huangJinState = sql_fetch_one_cell("select value from mem_state where state = 5 ");
			//	    		if($huangJinState == -1)
			//	    		{
			//	    			triggerHuangJinTask();
			//	    					
			//	    		}
			}
		}
	}
	if (sql_check ( "select * from log_battle_honour where uid='$uid' and battlefieldid='$battleinfo[battlefieldid]' and result=0 " ))
            $canwin = false;
	if ($result == $battleinfo ['unionid']) {
		//胜利退出
		//=====永久有效，一天重复一次
		switch($battleinfo ['bid']){//战场活动任务之战场犒赏
		   case 1001:{$mytid=101181;$goalid=19990;break;}
		   case 2001:{$mytid=101185;$goalid=19994;break;}
		   case 3001:{$mytid=101182;$goalid=19991;break;}
		   case 4001:{$mytid=101186;$goalid=19995;break;}
		   case 5001:{$mytid=101184;$goalid=19993;break;}
		   case 8001:{$mytid=101183;$goalid=19992;break;}
		}
		$usr_info=sql_fetch_one("select * from sys_goods where uid='$uid' and gid=$goalid");
	   if(empty($usr_info)){
	      sql_query("insert into sys_goods(uid,gid,count)values($uid,$goalid,1)");
	    }else{
		  sql_query("update sys_goods set count=count+1 where uid=$uid and gid=$goalid");
		}
		//====
		$winhonour = $level * $level * 8 * $baseRate;
		if (! $canwin) {
			$winhonour = 0;
		}
	} else if ($result == - 1) {
		//中途逃跑
		$winhonour = 0 - $level * $level * 20;
		$quittype = 2;
	} else {
		//失败退出
		$winhonour = 0 - $level * $level * 5;
		$quittype = 1;
	}
	if($result == $battleinfo ['unionid']){
		dropBattleGoods($uid,$battleinfo ['bid'],$level);
	}
	//	if(defined('ADULT') )
	if (isAdultOpen ()) {
		$punish = punishNotAdult ( $uid );
		if ($quittype != 1 && $quittype != 2) {
			$newhonour = intval ( $newhonour * $punish );
			$winhonour = intval ( $winhonour * $punish );
		}
	}
	
	if ($battleinfo ["state"] == 2) { //如果是准备状态的
		$quittype = 3;
	}
	if ($battleinfo ["type"] == 1) {
		//据点战场清理的时候再计算荣誉
		$winhonour = 0;
	}
	
	$nowhonour = $orihonour + $newhonour + $winhonour;
	sql_query ( "update sys_user set honour=$nowhonour where uid='$uid'" );
	
	if ($uid == $battleinfo ['createuid'] && ($quittype == 2 || $quittype == 3))//中途逃跑 转化队长
		autoTransferCaptain ( $uid );
	
	$battlefieldid = $battleinfo ["battlefieldid"];
	if ($battleinfo ["type"] == 1 && $quittype != 3) {
		sql_query ( "update sys_user_battle_state set state=1 where uid='$uid' and battlefieldid=$battlefieldid" );
	} else {
		sql_query ( "delete from sys_user_battle_state where uid='$uid' and battlefieldid=$battlefieldid" );
	}
	if ($battleinfo ['bid'] == 1001 || $battleinfo ['bid'] == 3001 || $battleinfo ['bid'] == 4001 || $battleinfo ['bid'] == 5001) {
		//如果是黄巾或者十常侍，没人了就直接给关掉
		$peopleCount = sql_fetch_one_cell ( "select count(*) from sys_user_battle_state where state=0 and battlefieldid='$battleinfo[battlefieldid]'" );
		if ($peopleCount == 0) {
			sql_query ( "update sys_user_battle_field set state=1 where id='$battleinfo[battlefieldid]'" );
			//对于最后一个人已经退出的十常侍副本，如果还没有决出胜负，必然副本失败
			sql_query ( "update sys_user_battle_field set winner=6 where id='$battleinfo[battlefieldid]' and bid=3001 and winner=-1" );
		}
	}
	$unionid = $battleinfo ['unionid'];
	$infounionid = $unionid;
	if ($battleinfo ['bid'] == 6001) {
		$infounionid = 25;
	}
	$unioninfo = sql_fetch_one ( "SELECT a.*,b.name AS metal_name FROM cfg_battle_union a LEFT JOIN cfg_things b ON a.metal_gid=b.tid WHERE a.unionid=$infounionid" );
	//勋章数目
	$xunzhangcount = 0;
	//发送消息
	$msg = "";
	$battlefieldname = $battleinfo ['name'];
	//勋章名
	$metalname = $unioninfo ['metal_name'];
	$metalgid = $unioninfo ['metal_gid'];
	$taskgroup = $unioninfo ['taskgroup'];
	$unionname = $unioninfo ['name'];
	/*
	 * 退出的时候不再清掉任务
	$tasks=sql_fetch_one_cell(" select group_concat(id) from cfg_task where `group` in ($taskgroup) " );
	if(!empty($tasks)){
		sql_query("delete from sys_user_task where uid=$uid and tid in ($tasks)");
		$taskgoals=sql_fetch_one_cell(" select group_concat(id) from cfg_task_goal where tid in ($tasks) " );
		sql_query("delete from sys_user_goal where uid=$uid and gid in ($taskgoals)");
	}
	*/
	$msg = "";
	if ($quittype == 0) {
		//完成战场随机任务
		if ($battleinfo ['bid'] == 1001) //黄巾之乱
{
			completeTaskGoalBySortandType ( $uid, 54, 1 );
		} else if ($battleinfo ['bid'] == 2001) //官渡
{
			completeTaskGoalBySortandType ( $uid, 54, 2 );
		}
		
		//如果胜利，加勋章
		$xunzhangcount = $battleinfo ["level"];
		if ($uid == $battleinfo ['createuid']) {
			//创建者可多获得勋章
			if ($battleinfo ["level"] > 10)
				$xunzhangcount += $battleinfo ["level"] - 10;
		}
		if (! $canwin) {
			$xunzhangcount = 0;
		}
		if ($battleinfo ["type"] == 0) {
			$xunzhangcount = $xunzhangcount * $baseRate;
			//			if(defined('ADULT') )
			if (isAdultOpen ()) {
				$punish = punishNotAdult ( $uid );
				if ($quittype != 1 && $quittype != 2) {
					$xunzhangcount = intval ( $xunzhangcount * $punish );
				}
			}
			addThings ( $uid, $metalgid, $xunzhangcount, 4 );
			$msg = sprintf ( $GLOBALS ['battle'] ['pve_quit_win'], $battlefieldname, $winhonour, $newhonour, $metalname, $xunzhangcount, $nowhonour );
		} else {
			$msg = sprintf ( $GLOBALS ['battle'] ['pvp_quit_win'], $battlefieldname, $winhonour, $newhonour, $nowhonour );
		}
//战场活动ACT begin
		if($canwin){
		    $myrand=mt_rand(1,100);
		    if($myrand>90 && $battleinfo ['bid']==1001){//对于12级的黄巾之战赠送乾坤珠宝
			     $goalid=10414;
			     $usr_info=sql_fetch_one("select * from sys_goods where uid='$uid' and gid=$goalid");
	             if(empty($usr_info)){
	                 sql_query("insert into sys_goods(uid,gid,count)values($uid,$goalid,1)");
	                }else{
		              sql_query("update sys_goods set count=count+1 where uid=$uid and gid=$goalid");
		            }
			}
			$battleActs = getAvailableBattleActs();
			foreach ($battleActs as $battleAct){
				$actType = $battleAct['type'];
				$actRate = $battleAct['rate'];
				$actid = $battleAct['actid'];
				$levelRate = false;
				if($actType%100==20)//处理等级概率
				{
					$levelRate = true;
					$actRate=$level*$actRate;
				}
				if($battleinfo['bid']==floor(($actType-3000)/100)*1000+1 && ($level==$actType%100||$actType%100==0 || $levelRate) && isLucky($actRate,100)){//actype: 3211表示bid=2001的12级战场
					$cid = sql_fetch_one_cell("select lastcid from sys_user where uid=$uid");
					$ext_msg = openDefaultBox($uid, $cid, $actid, 3000);
					if($ext_msg){
						$ext_msg=$ext_msg."。<br/>";
					}
				}
			}
		}
//战场活动ACT end
		try {
			$msg =$msg."<br/>".$ext_msg;
		} catch ( Exception $e ) {
			error_log ( $e );
		}
	} else if ($quittype == 1) {
		if ($battleinfo ["type"] == 0) {
			$msg = sprintf ( $GLOBALS ['battle'] ['quit_lose'], $battlefieldname, (0 - $winhonour), $newhonour, $nowhonour );
			if ($battleinfo ["bid"] == 5001) {
				$msg = $GLOBALS ['battle'] ['5001_quit_lose'] . $msg;
			}
		} else {
			$msg = sprintf ( $GLOBALS ['battle'] ['pvp_quit_lose'], $battlefieldname, $newhonour, $nowhonour );
		}
	} else if ($quittype == 2) {
		if ($battleinfo ["type"] == 0) {
			$msg = sprintf ( $GLOBALS ['battle'] ['quit_leave'], $battlefieldname, (0 - $winhonour), $newhonour, $nowhonour );
		} else {
			$msg = sprintf ( $GLOBALS ['battle'] ['pvp_quit_leave'], $battlefieldname, $newhonour, $nowhonour );
		}
	} else if ($quittype == 3) {
		$msg = sprintf ( $GLOBALS ['battle'] ['quit_leave_notstartbattle'], $battlefieldname, $orihonour );
	}
	
	//清理用户副本任务中得到的任务道具
	$taskthing = $unioninfo ['taskthing'];
	if(!empty($taskthing)) {
		sql_query ( "delete from sys_things where uid=$uid and tid in ($taskthing)" );
		sql_query ( "delete from sys_troop_things where uid=$uid and thing_id in ($taskthing)" );
	}
	
	//荣誉和勋章log
	//$thishonour=$nowhonour-$orihonour;
	if ($quittype != 3)
		sql_query ( "insert into log_battle_honour (uid,battleid,battlefieldid,starttime,quittime,result,honour,metal,unionid,level) values ($uid,'$battleinfo[bid]','$battleinfo[battlefieldid]','$battleinfo[starttime]',unix_timestamp(),$quittype,$nowhonour,$xunzhangcount,'$battleinfo[unionid]','$battleinfo[level]') " );
	
	playerExitBattle ( $battleinfo ['battlefieldid'], $battleinfo ['unionid'], $uid, $nick, $unionname );
	
	//发战报
	if ($battleinfo ["type"] == 1 && $quittype != 3) {
		$msg = $msg . $GLOBALS ['battle'] ['medal_info_after'];
		sendReport ( $uid, 0, 48, 0, 0, $msg );
	} else {
		sendReport ( $uid, 0, 40, 0, 0, $msg );
	}
	$ret = array ();
	$ret [] = $GLOBALS ['battle'] ['exit_suc'];
	
	return $ret;
	//战场荣誉减少
}
function dropBattleGoods($uid,$battleid,$level){
					if($battleid== "2001"||$battleid == "1001"){
						if($level==5){
							 $goodsql="select * from cfg_act_task_good_drop where param='".$level."' and unix_timestamp()>starttime and unix_timestamp()<endtime and actiontype='".$battleid."'";
							 $goods=sql_fetch_rows($goodsql);
							foreach ($goods as $good){
								 $gid=$good["type"];
								 $loggoodssql="select sum(`count`) from  log_goods where uid='".$uid."' and type='".$battleid."' and curdate()=date(from_unixtime(time))";
								 $dayCount=sql_fetch_one_cell($loggoodssql);
								if ($dayCount<3)
								{
									$count=$good["count"];
									$type=$battleid;
									if ($gid >0 && $count != 0) {
										addGoods($uid,$gid,$count,$type);
										if ($type == 1) {
											sql_query("insert into log_battle_drop (uid,`count`,time) values ('$uid','$count',unix_timestamp()) on duplicate key update count=count+$count, time=unix_timestamp()");
										}
									}
								}
							}

						}
					}
}
/**
 * 调遣援军
 *
 * @param unknown_type $uid
 * @param unknown_type $param
 * @return unknown
 */
function callArmy($uid, $param) {
	
	$troopid = intval ( array_shift ( $param ) );
	$sid = intval ( array_shift ( $param ) );
	$count = intval ( array_shift ( $param ) );
	$battlefieldinfo = ifFrozeExit ( $uid );
	$troop = sql_fetch_one ( "select * from sys_troops where id='$troopid' and uid='$uid' and battlefieldid='$battlefieldinfo[battlefieldid]' " );
	
	if (empty ( $troop )) {
		throw new Exception ( $GLOBALS ['battle'] ['troop_not_exist'] );
	}
	if ($troop ['state'] == 3) {
		throw new Exception ( $GLOBALS ['battle'] ['troop_in_fight'] );
	}
	if ($troop ['state'] == 1) {
		throw new Exception ( $GLOBALS ['battle'] ['troop_in_back_no_call'] );
	}
	
	if ($troop ['task'] == 7 && ($troop ['state'] == 0 || $troop ['state'] == 1)) {
		throw new Exception ( $GLOBALS ['battle'] ['troop_in_forward_no_call'] );
	}
	$SOLDIER_PEOPLE = array (0, 1, 1, 1, 1, 1, 2, 3, 6, 4, 5, 10, 8 );
	$SOLDIER_VALUE = array (0, 22.5, 30.5, 69.5, 90, 135, 140, 285, 875, 297.5, 1000, 1375, 2900 );
	
	$count1 = $battlefieldinfo ["honour"] * 100 / $SOLDIER_PEOPLE [$sid];
	$allpeople = getSoldierPeople ( $troop ["soldiers"] );
	//$count2=$troop["ulimit"]-$allpeople;
	$count2 = $GLOBALS ['battle'] ['soldier_limit'] [$battlefieldinfo ["level"]] - $allpeople;
	$maxCall = min ( $count1, $count2 );
	
	if ($count > $maxCall) {
		throw new Exception ( $GLOBALS ['battle'] ['call_army_max'] );
	}
	
	$callPeople = $count * $SOLDIER_PEOPLE [$sid];
	
	$needHonour = ceil ( $callPeople / 100 );
	if ($needHonour > $battlefieldinfo ["honour"]) {
		throw new Exception ( $GLOBALS ['battle'] ['call_army_not_enough_honour'] );
	}
	
	$yuanjun = 0;
	$value = $count * $SOLDIER_VALUE [$sid];
	
	if ($value == 0)
		$yuanjun = 0;
	else if ($value <= 100000)
		$yuanjun = 1;
	else if ($value <= 1000000)
		$yuanjun = 2;
	else if ($value <= 10000000)
		$yuanjun = 3;
	else if ($value <= 100000000)
		$yuanjun = 4;
	else
		$yuanjun = 5;
	
	if (! checkGoodsCount ( $uid, 135, $yuanjun )) {
		throw new Exception ( "not_enough_goods135#$yuanjun" );
	}
	/*
	if(!enoughIntervalFromLastMark($troop["battlefieldid"],3,135,true,300)){
		throw new Exception($GLOBALS['battle']['yuanjun_not_enough_interval']);
	}
	*/
	$ret = array ();
	$newsoldiers = enhanceArmy ( $troop ["id"], $troop ["soldiers"], $sid, $count, $troop ["startcid"], $troop ["hid"],$battlefieldinfo['bid']);
	if ($newsoldiers) {
		reduceGoods ( $uid, 135, $yuanjun );
		//减去战场荣誉
		

		sql_query ( "update sys_user set honour=honour-$needHonour where uid='$uid'" );
		$ret [] = 1;
		$ret [] = $troopid;
		$ret [] = $newsoldiers;
		$ret [] = $battlefieldinfo ["honour"] - $needHonour;
		$ret [] = $GLOBALS ['battle'] ['call_army_suc'];
	} else {
		$ret [] = 0;
		$ret [] = $GLOBALS ['battle'] ['call_army_fail'];
		unlockUser ( $uid );
	}
	
	return $ret;

}

function reGeneratePathTime($cid, $hid, $soldiers) {
	//步兵速度加成
	$speedAddRate1 = 1 + intval ( sql_fetch_one_cell ( "select level from sys_city_technic where cid='$cid' and tid=12" ) ) * 0.1;
	//骑兵速度加成
	$speedAddRate2 = 1 + intval ( sql_fetch_one_cell ( "select level from sys_city_technic where cid='$cid' and tid=13" ) ) * 0.05;
	//将领速度加成
	$speedAddRate3 = 1;
	if ($hid != 0) {
		$heroInfo = sql_fetch_one ( "select * from sys_city_hero where hid=$hid" );
		$speedAddRate3 = 1 + $heroInfo ['speed_add_on'] * 0.01;
	}
	
	$soldierArray = explode ( ",", $soldiers );
	$numSoldiers = array_shift ( $soldierArray );
	$takeSoldiers = array (); //真正带出去的军队
	for($i = 0; $i < $numSoldiers; $i ++) {
		$sid = array_shift ( $soldierArray );
		$cnt = array_shift ( $soldierArray );
		if ($cnt < 0)
			$cnt = 0;
		$takeSoldiers [$sid] = $cnt;
	}
	
	//单程时间 ＝ 每格子距离/最慢兵种速度+宿营时间（每格距离＝60000/game_speed_rate）
	$pathLength = 162000;
	$minSpeed = 999999999;
	// TODO 可以优化和缓存
	$soldierConfig = sql_fetch_rows ( "select * from cfg_soldier where fromcity=1 order by sid", "sid" );
	foreach ( $soldierConfig as $soldier ) //找到当前军队里最慢的
{
		if ($hid > 0) {
			//计算将领身上因为装备而得到的该士兵的速度加成
			$sid = $soldier->sid;
			$speedAdd = 0;
			if ($sid >= 1 && $sid <= 12) {
				$attid = 2000 + ($sid - 1) * 100 + 11; //取得属性id
				$attr = sql_fetch_one ( "select * from sys_hero_attribute where hid=$hid and attid=$attid" );
				if (! empty ( $attr )) {
					$speedAdd = $attr ['value'];
				}
			}
		}
		if (! empty ( $takeSoldiers [$soldier->sid] )) {
			//除了斥候外的步兵速度加成
			if ($soldier->sid < 7 && $soldier->sid != 3) {
				$minSpeed = min ( $soldier->speed * $speedAddRate1 * $speedAddRate3 + $speedAdd, $minSpeed );
			} //骑兵加成
			else {
				$minSpeed = min ( $soldier->speed * $speedAddRate2 * $speedAddRate3 + $speedAdd, $minSpeed );
			}
		}
	}
	
	$pathNeedTime = $pathLength / $minSpeed; //需要多少时间
	

	$pathNeedTime = intval ( floor ( $pathNeedTime ) );
	return $pathNeedTime;
}

function fasterChibiArmy($uid,$param){
	$troopid = intval(array_shift($param));
	$battlefieldid = array_shift($param);
	if($battlefieldid == 11000){
		$chibipara = array();
		$chibipara[] = $troopid;
		$chibipara[] = 160019;
		return fastChiBiArmy($uid,$chibipara);
	}
}
function fasterMenghuoArmy($uid,$param){
	$troopid = intval(array_shift($param));
	$battlefieldid = array_shift($param);
	if($battlefieldid == 9001){
		$chibipara = array();
		$chibipara[] = $troopid;
		$chibipara[] = 161002;
		return fastMenghuoArmy($uid,$chibipara);
	}
}
function getFasterGoodInfo($uid,$param)
{
	$gid = intval(array_shift($param));
	$ret = array();
	$ret[] = sql_fetch_one("select * from cfg_shop where onsale = 1 and gid = $gid");
	return $ret;
}
/**
 * 加速行军
 *
 * @param unknown_type $uid
 * @param unknown_type $param
 */
function fasterArmy($uid, $param) {
	$troopid = intval ( array_shift ( $param ) );
	$battlefieldid=intval(array_shift($param));
	$battlefieldinfo = ifFrozeExit ( $uid );
	
	$troop = sql_fetch_one ( "select *,unix_timestamp() as now from sys_troops where id='$troopid' and uid='$uid' and battlefieldid='$battlefieldinfo[battlefieldid]' " );
	if (empty ( $troop )) {
		throw new Exception ( $GLOBALS ['battle'] ['troop_not_exist'] );
	}
	if ($troop ['state'] != 0 && $troop ['state'] != 1) {
		throw new Exception ( $GLOBALS ['battle'] ['troop_not_in_move'] );
	}
	
	if ($troop ['endtime'] - $troop ['now'] < 10) {
		throw new Exception ( $GLOBALS ['battle'] ['troop_not_need_faster'] );
	}
	
	if (! checkGoods ( $uid, 136, 1 )) {
		throw new Exception ( "not_enough_goods136" );
	}
	
	$result = sql_query ( "update sys_troops set endtime=unix_timestamp()+10 where id='$troopid'" );
	$ret = array ();
	$troopinfo = sql_fetch_one ( "select pathtime,endtime,state from sys_troops where id='$troopid'" );
	
	if ($troopinfo ['state'] == 0) {
		$troopinfo ['state'] = $GLOBALS ['battle'] ['state_0'];
	} else if ($troopinfo ['state'] == 1) {
		$troopinfo ['state'] = $GLOBALS ['battle'] ['state_1'];
	} else if ($troopinfo ['state'] == 2) {
		$troopinfo ['state'] = $GLOBALS ['battle'] ['state_2'];
		$troopinfo ['pathtime'] = "--";
		$troopinfo ['endtime'] = "--";
	} else if ($troopinfo ['state'] == 3) {
		$troopinfo ['state'] = $GLOBALS ['battle'] ['state_3'];
		$troopinfo ['pathtime'] = "--";
		$troopinfo ['endtime'] = "--";
	} else if ($troopinfo ['state'] == 4) {
		$troopinfo ['state'] = $GLOBALS ['battle'] ['state_4'];
		$troopinfo ['pathtime'] = "--";
		$troopinfo ['endtime'] = "--";
	}
	$ret [] = $troopinfo;
	if ($result) {
		reduceGoods ( $uid, 136, 1 );
		$ret [] = $GLOBALS ['battle'] ['faster_army_suc'];
	} else {
		$ret [] = $GLOBALS ['battle'] ['faster_army_fail'];
		unlockUser ( $uid );
	}
	return $ret;

}
//处理远程加速军队返回的跨服务结果到客户端
function  fastRemoteArmy($uid,$param,$useResult){
	$ret=array();
	$ret [] = &$useResult;
	switch($useResult){
		case -1: {
			throw new Exception ( $GLOBALS ['battle'] ['troop_not_exist'] );
			break;
		}
		case -2: {
			throw new Exception ( $GLOBALS ['battle'] ['troop_not_in_move'] );
			break;
		}
		case -3: {
			throw new Exception ( $GLOBALS ['battle'] ['troop_not_need_faster'] );
			break;
		}
		case -4: {
			$ret[] = 1;
			$goodname=sql_fetch_one_cell("select name from cfg_goods where gid=".$param[1]);
			$ret[] = $GLOBALS['battle']['troop_not_enough_some_good'].$goodname.$GLOBALS['battle']['troop_not_enough_some_good_buy'];
			return $ret;
			break;
		}
		case -5: {
			$ret [] = $GLOBALS ['battle'] ['faster_army_fail'];
			break;
		}
		default:{
			if ($useResult ['state'] == 0) {
				$useResult ['state'] = $GLOBALS ['battle'] ['state_0'];
			} else if ($useResult ['state'] == 1) {
				$useResult ['state'] = $GLOBALS ['battle'] ['state_1'];
			} else if ($useResult ['state'] == 2) {
				$useResult ['state'] = $GLOBALS ['battle'] ['state_2'];
				$useResult ['pathtime'] = "--";
				$useResult ['endtime'] = "--";
			} else if ($useResult ['state'] == 3) {
				$useResult ['state'] = $GLOBALS ['battle'] ['state_3'];
				$useResult ['pathtime'] = "--";
				$useResult ['endtime'] = "--";
			} else if ($useResult ['state'] == 4) {
				$useResult ['state'] = $GLOBALS ['battle'] ['state_4'];
				$useResult ['pathtime'] = "--";
				$useResult ['endtime'] = "--";
			}
	//		$ret [] = $troopinfo;
	//		setTroopInfo($useResult);
	//		reduceGoods ( $uid, 136, 1 );
			$ret[] = 0;
			$ret [] =$GLOBALS['battle']['faster_chibi_army_suc'];
		}
	}
	return $ret;
}
function fastMenghuoArmy($uid,$param){
	$useResult= sendRemote9001Request($uid,"fastTroop",$param);
	return fastRemoteArmy($uid,$param,$useResult);
}
function fastChiBiArmy($uid,$param){
	//$param 里面必需包括troopid，gid
//	if (! checkGoods ( $uid, 136, 1 )) {
//		throw new Exception ( "not_enough_goods136" );
//	}
	
	$useResult=sendChibiRemoteRequest($uid,"fastTroop",$param);
	return fastRemoteArmy($uid,$param,$useResult);
//	$ret=array();
//	$ret [] = &$useResult;
//	switch($useResult){
//		case -1: {
//			throw new Exception ( $GLOBALS ['battle'] ['troop_not_exist'] );
//			break;
//		}
//		case -2: {
//			throw new Exception ( $GLOBALS ['battle'] ['troop_not_in_move'] );
//			break;
//		}
//		case -3: {
//			throw new Exception ( $GLOBALS ['battle'] ['troop_not_need_faster'] );
//			break;
//		}
//		case -4: {
//			$ret[] = 1;
//			$ret[] = $GLOBALS['battle']['troop_not_enough_jixingjun'];
//			return $ret;
//			break;
//		}
//		case -5: {
//			$ret [] = $GLOBALS ['battle'] ['faster_army_fail'];
//			break;
//		}
//		default:{
//			if ($useResult ['state'] == 0) {
//				$useResult ['state'] = $GLOBALS ['battle'] ['state_0'];
//			} else if ($useResult ['state'] == 1) {
//				$useResult ['state'] = $GLOBALS ['battle'] ['state_1'];
//			} else if ($useResult ['state'] == 2) {
//				$useResult ['state'] = $GLOBALS ['battle'] ['state_2'];
//				$useResult ['pathtime'] = "--";
//				$useResult ['endtime'] = "--";
//			} else if ($useResult ['state'] == 3) {
//				$useResult ['state'] = $GLOBALS ['battle'] ['state_3'];
//				$useResult ['pathtime'] = "--";
//				$useResult ['endtime'] = "--";
//			} else if ($useResult ['state'] == 4) {
//				$useResult ['state'] = $GLOBALS ['battle'] ['state_4'];
//				$useResult ['pathtime'] = "--";
//				$useResult ['endtime'] = "--";
//			}
//	//		$ret [] = $troopinfo;
//	//		setTroopInfo($useResult);
//	//		reduceGoods ( $uid, 136, 1 );
//			$ret[] = 0;
//			$ret [] =$GLOBALS['battle']['faster_chibi_army_suc'];
//		}
//	}
//	return $ret;
}

function battleAttack($uid, $param) {
	$troopid = intval ( array_shift ( $param ) );
	$targettroopid = intval ( array_shift ( $param ) );
	$heroname = array_shift ( $param );
	$heroname=addslashes($heroname);
	$targetcityname = array_shift ( $param );
	$targetcityname = addslashes($targetcityname);
	$battlefieldinfo = ifFrozeExit ( $uid );
	
	if ($battlefieldinfo ['type'] == 1 && $battlefieldinfo ['state'] == 2) {
		throw new Exception ( $GLOBALS ['battle'] ['battle_in_ready'] );
	}
	
	if ($battlefieldinfo ['bid'] == 6001 && $battlefieldinfo ['progress'] == 0) {
		throw new Exception ( $GLOBALS ['battle'] ['battle_in_dispatch'] );
	}
	$troop = sql_fetch_one ( "select * from sys_troops where id='$troopid' and uid='$uid' and battlefieldid='$battlefieldinfo[battlefieldid]' " );
	if (empty ( $troop )) {
		throw new Exception ( $GLOBALS ['battle'] ['troop_not_exist'] );
	}
	if ($troop ['state'] != 4) {
		throw new Exception ( $GLOBALS ['battle'] ['troop_not_stay'] );
	}
	
	//检查目的部队
	$targettroop = sql_fetch_one ( "select * from sys_troops where id='$targettroopid'" );
	if (empty ( $targettroop )) {
		throw new Exception ( $GLOBALS ['battle'] ['targettroop_not_exist'] );
	}
	
	//检查出发据点
	$origincity = sql_fetch_one ( "select * from sys_battle_city where cid='$troop[cid]'" );
	
	if (empty ( $origincity )) {
		throw new Exception ( $GLOBALS ['battle'] ['city_not_exist'] );
	}
	$targetcity = sql_fetch_one ( "select * from sys_battle_city where cid='$targettroop[cid]'" );
	if (empty ( $targetcity )) {
		throw new Exception ( $GLOBALS ['battle'] ['city_not_exist'] );
	}
	$targetcid = $targetcity ['cid'];
	
	//是否是我方阵营
	$unionid = $battlefieldinfo ['unionid'];
	if ($targettroop ['battleunionid'] == $unionid) {
		throw new Exception ( $GLOBALS ['battle'] ['same_union'] );
	}
	
	$endtime = "unix_timestamp()+pathtime";
	if ($targetcity ['cid'] == $origincity ['cid']) {
		//同一据点内相互攻击
		//TODO
		$endtime = "unix_timestamp()+10";
	} else if (! canGoto ( $targetcid, $origincity ['nextxy'] )) {
		throw new Exception ( $GLOBALS ['battle'] ['city_cannot_goto'] );
	}
	if ($targetcity ['cid'] != $origincity ['cid']) {
		if ($battlefieldinfo ['bid'] == 2001) {
			//如果是官渡，则要检查
			$xy = $targettroop ['cid'] % 1000;
			if ($xy == 767) {
				//攻击许都，检查曹军粮草是否耗尽
				$point = sql_fetch_one_cell ( "select point from sys_battle_winpoint where battlefieldid='$battlefieldinfo[battlefieldid]' and unionid=4" );
				if ($point > 0) {
					throw new Exception ( $GLOBALS ['battle'] ['cao_has_food'] );
				}
			} else if ($xy == 101) {
				//攻击袁绍1，或者袁绍3，检查曹军粮草是否耗尽
				$point = sql_fetch_one_cell ( "select point from sys_battle_winpoint where battlefieldid='$battlefieldinfo[battlefieldid]' and unionid=3" );
				if ($point > 0) {
					throw new Exception ( $GLOBALS ['battle'] ['yuan_has_food'] );
				}
			}
		} else if ($battlefieldinfo ['bid'] == 3001) {
			//如果是十常侍，攻击洛阳之前必须检查搜集罪证的任务是否已经完成了
			//需要检查被攻击的城市是否属于攻击锁定状态
			$cid = $targettroop ['cid'] % 1000;
			$battlefieldid = $battlefieldinfo ['battlefieldid'];
			$attack_lock = sql_fetch_one ( "SELECT * FROM mem_battle_attack_lock WHERE battlefieldid='$battlefieldid' AND cid='$cid'" );
			if (! empty ( $attack_lock )) {
				throw new Exception ( $attack_lock ['msg'] );
			}
		} else if ($battlefieldinfo ['bid'] == 4001) {
			$cid = $targettroop ['cid'] % 1000;
			$battlefieldid = $battlefieldinfo ['battlefieldid'];
			$attack_lock = sql_fetch_one ( "SELECT * FROM mem_battle_attack_lock WHERE battlefieldid='$battlefieldid' AND cid='$cid' AND uid='$uid'" );
			if (! empty ( $attack_lock )) {
				throw new Exception ( $attack_lock ['msg'] );
			}
			
			//如果是董卓副本，身上有玉玺的话需要检查是否停留的够久
			

			$yuxi = sql_fetch_one ( "select * from sys_troop_things where troop_id=$troopid and thing_id=45" );
			if (! empty ( $yuxi )) {
				$arrive_time = $troop ['arrive_time'];
				$timediff = sql_fetch_one_cell ( "select unix_timestamp()-arrive_time from sys_troops where id=$troopid" );
				if (intval ( $timediff ) < 60) {
					throw new Exception ( $GLOBALS ['battle'] ['must_stay_one_minute'] );
				}
			}
		} else {
			$cid = $targettroop ['cid'] % 1000;
			$battlefieldid = $battlefieldinfo ['battlefieldid'];
			$attack_lock = sql_fetch_one ( "SELECT * FROM mem_battle_attack_lock WHERE battlefieldid='$battlefieldid' AND cid='$cid' AND (uid in ('$uid',0) or uid IS NULL)" );
			if (! empty ( $attack_lock )) {
				throw new Exception ( $attack_lock ['msg'] );
			}
		}
	}
	
	$result = sql_query ( "update sys_troops set starttime=unix_timestamp(),state=0,targetcid='$targetcity[cid]',task=9,targettroopid='$targettroopid',endtime=$endtime,arrive_time=0 where id='$troopid' " );
	if ($result) {
		$ret [] = $GLOBALS ['StartTroop'] ['succ'];
		
		if ($targetcity ['cid'] != $origincity ['cid']) {
			//$fieldname=$GLOBALS['battle']['union_name'][$unionid];
		//playTroopLeave($battlefieldinfo['battlefieldid'], $unionid, $fieldname, $targetcityname,$heroname)	;
		}
		resetBattleFieldUid ( $troop ['cid'] );
	
	} else
		$ret [] = $GLOBALS ['StartTroop'] ['fail'];
	return $ret;

}

/**
 * 计算调遣援军的信息
 *
 * @param unknown_type $uid
 * @param unknown_type $param
 */
function getCallArmyInfo($uid, $param) {
	$battlefieldinfo = ifFrozeExit ( $uid );

}

function userJiXingJun($uid, $param) {
	$troopid = intval ( array_shift ( $param ) );
	$troop = sql_fetch_one ( "select * from sys_troops where id='$troopid' and uid='$uid' " );
}

function getBattleInvite($uid, $param) {
	$ret = array ();
	$ret [] = sql_fetch_rows ( "select * from sys_battle_invite where touid='$uid' and state=0 order by time desc" );
	return $ret;
}

//判断用户是否满足战场要求,add by Guan
function checkUserQualityForJoinBattle($uid, $bid) {
	
	$battleDemands = sql_fetch_one ( "select demandGoods, demandThings,demandNobility from cfg_battle_field where id = $bid" );
	$nobility = intval ( sql_fetch_one_cell ( "select nobility from sys_user  where uid = $uid" ) );
	$nobility = getBufferNobility ( $uid, $nobility );
	
	if ($nobility >= $battleDemands [demandNobility]) {
		
		$sqlCommand1 = "select name,gid from cfg_goods where 1=2 ";
		$sqlCommand2 = "where 1=2 ";
		$goodsArray = array ();
		$thingsArray = array ();
		
		$token = strtok ( $battleDemands ['demandGoods'], "," );
		
		while ( $token !== false ) {
			$goodId = substr ( $token, 0, strpos ( $token, ":" ) );
			$goodCount = substr ( $token, strpos ( $token, ":" ) + 1 );
			
			$sqlCommand1 = "$sqlCommand1 or gid=$goodId ";
			$sqlCommand2 = "$sqlCommand2 or ( T.gid=$goodId and (S.count is null or S.count < $goodCount) )";
			
			$goodsArray [$goodId] = $goodCount;
			$token = strtok ( "," );
		}
		
		$sqlCommand = "select T.name ,S.count from ($sqlCommand1)T left join (select gid,`count` from sys_goods where uid = $uid )S on T.gid = S.gid $sqlCommand2  limit 0,1";
		
		$goodName = sql_fetch_one ( $sqlCommand );
		if (! empty ( $goodName )) {
			throw new Exception ( sprintf ( $GLOBALS ['battle'] ['join_resource_not_enough'], $goodName ['name'] ) );
		}
		
		$sqlCommand1 = "select name,tid from cfg_things where 1=2 ";
		$sqlCommand2 = "where 1=2 ";
		
		$token = strtok ( $battleDemands ['demandThings'], "," );
		while ( $token !== false ) {
			$thingId = substr ( $token, 0, strpos ( $token, ":" ) );
			$thingCount = substr ( $token, strpos ( $token, ":" ) + 1 );
			
			$sqlCommand1 = "$sqlCommand1 or tid=$thingId ";
			$sqlCommand2 = "$sqlCommand2 or ( T.tid=$thingId and (S.count is null or S.count < $thingCount) )";
			
			$thingsArray [$thingId] = $thingCount;
			$token = strtok ( "," );
		}
		$sqlCommand = "select T.name ,S.count from ($sqlCommand1)T left join (select tid,`count` from sys_things where uid = $uid )S on T.tid = S.tid $sqlCommand2  limit 0,1";
		
		$thingName = sql_fetch_one ( $sqlCommand );
		if (! empty ( $thingName )) {
			throw new Exception ( sprintf ( $GLOBALS ['battle'] ['join_resource_not_enough'], $thingName ['name'] ) );
		}
		
		foreach ( $goodsArray as $goodId => $goodCount ) {
			reduceGoods ( $uid, $goodId, $goodCount );
		
		}
		foreach ( $thingsArray as $thingId => $thingCount ) {
			reduceThings ( $uid, $thingId, $thingCount );
		}
	
	} else {
		throw new Exception ( $GLOBALS ['battle'] ['join_nobility_not_qualified'] );
	}
}

function joinToBattle($uid, $param) {
	$inviteid = intval ( array_shift ( $param ) );
	$inviteinfo = sql_fetch_one ( "select id, battlefieldid,toname,touid from sys_battle_invite where id='$inviteid' and touid='$uid'" );
	
	if (empty ( $inviteinfo )) {
		throw new Exception ( $GLOBALS ['battle'] ['no_such_invite'] );
	}
	
	if (sql_check ( "select * from sys_user_battle_state where state=0 and uid='$uid'" )) {
		throw new Exception ( $GLOBALS ['battle'] ['join_user_already_in_battle'] );
	}
	if(defined("BATTLE_NET_ENABLE") && BATTLE_NET_ENABLE){
		$waitingBid = sql_fetch_one_cell("select bid from sys_battlenet_waiting_queue where uid='$uid'");
		if ($waitingBid>0) {
			throw new Exception ( $GLOBALS['battle']['already_in_battlenet'] );
		}
//		$battlenetinfo = sendRemoteRequest ( $uid, "getUserBattleState" );
//		if ($battlenetinfo [0] == 2) {
//			throw new Exception ( $GLOBALS['battle']['already_in_battlenet'] );
//		}
	}
    $battlefieldid = $inviteinfo ['battlefieldid'];
    $battlefieldinfo = sql_fetch_one ( "select * from sys_user_battle_field where id='$battlefieldid' and state=0" );
    if (empty ( $battlefieldinfo )) {
    	//检查9001房间
    	$params = array();
    	$params[] = intval($battlefieldid);
    	$hasRoom = sendRemote9001Request($uid, "checkMenghuoRemoteRoom", $params);
    	if(!$hasRoom)
        	throw new Exception ( $GLOBALS ['battle'] ['no_battle_field'] );
        $bid = 9001;
    }
	if($bid == 9001) {
//    	$params = array();
//		$params[] = 9001;
//		$params[] = intval($inviteinfo['battlefieldid']);
//		$remoteRet = sendRemote9001Request($uid,"addUser",$params);
//    	$room_id = array_shift($remoteRet);
    	
		sql_query ( "delete from sys_battle_invite where id='$inviteinfo[id]' " );
		$ret = array();
		$ret[] = 9001;
		$ret[] = $battlefieldid;
		return $ret;
    }
//    if($bid == 9001) {
//    	$params = array();
//		$params[] = 9001;
//		$params[] = intval($inviteinfo['battlefieldid']);
//		$remoteRet = sendRemote9001Request($uid,"addUser",$params);
//    	$room_id = array_shift($remoteRet);
//    	
//		sql_query ( "delete from sys_battle_invite where id='$inviteinfo[id]' " );
//		$ret = array();
//		$ret[] = 9001;
//		$ret[] = $room_id;
//		return $ret;
//    }
	$bid = $battlefieldinfo['bid'];
    if($bid == 8001) {
         //凤仪亭,只有当创建者还没有完成第一个主线任务的时候才可以加入
          $gid=sql_fetch_one_cell("select id from cfg_task_goal where tid=60800");
            if(sql_check("select 1 from sys_user_goal where uid='$battlefieldinfo[createuid]' and gid=$gid")
            || $battlefieldinfo["progress"]!=0){
                throw new Exception ("已有玩家完成第一个主线任务，此时已经不能加入到副本中" );
            }
         
    }
	$todayWarCont = sql_fetch_one_cell ( "select today_war_count from mem_user_schedule where uid = $uid" );
	if (empty ( $todayWarCont ))
		$todayWarCont = 0;
	if ($todayWarCont >= 5) {
		throw new Exception ( $GLOBALS ['battle'] ['today_war_count_reach_limit'] );
	}

	
	$unionid = sql_fetch_one_cell ( "select user_unionid from cfg_battle_field where id=$battlefieldinfo[bid]" );
	$unioninfo = sql_fetch_one ( "select * from cfg_battle_union where unionid=$unionid" );
	
	$joincount = sql_fetch_one_cell ( "select count(*) from sys_user_battle_state where state=0 and battlefieldid='$battlefieldid'" );
	if ($joincount >= 5) {
		throw new Exception ( $GLOBALS ['battle'] ['user_full'] );
	}
	
	
	$currenthonour = sql_fetch_one_cell ( "select honour from sys_user where uid='$uid'  " );
	////$currenthonour=0;
	if (empty ( $currenthonour )) {
		$currenthonour = 0;
	}
	if ($currenthonour < 0) {
		throw new Exception ( $GLOBALS ['battle'] ['honour_invalid'] );
	}
	

	$fieldname = $unioninfo ["name"];
	//$battlestartcid=sql_fetch_one_cell("select * from cfg_battle_field where id=$bid");
	//$startcid=battleid2cid($battlefieldid,$battlestartcid);
	checkUserQualityForJoinBattle ( $uid, $battlefieldinfo ['bid'] );
	if (! lockUser ( $uid ))
		throw new Exception ( $GLOBALS ['pacifyPeople'] ['server_busy'] );
	$result = sql_query ( "insert into sys_user_battle_state (uid,bid,battlefieldid,unionid,level) values($uid,$bid,$battlefieldid,$unionid,'$battlefieldinfo[level]') on duplicate key update battlefieldid=$battlefieldid,bid=$bid,unionid=$unionid,level='$battlefieldinfo[level]'" );
	if ($result) {
		deleteOldBattleTasks ( $uid );
		if ($bid == 1001) {
			//黄巾之乱，添加任务
			$tasks = sql_fetch_rows ( "select id from cfg_task where `group` in (60000,60001,60002,60003,60004)  and pretid=-1" );
			foreach ( $tasks as $task ) {
				sql_query ( "insert into sys_user_task (uid,tid,state) values ($uid,'$task[id]',0) on duplicate key update state=0" );
			}
		} else if ($bid == 3001 ) {
			//十常侍之乱,只添加战场创建者没有完成的任务
			$tasks = sql_fetch_rows ( "SELECT tid FROM sys_user_task WHERE uid='$battlefieldinfo[createuid]' AND tid>=60000 AND tid<=70000 AND NOT EXISTS (SELECT * FROM sys_user_goal,cfg_task_goal WHERE sys_user_goal.uid=sys_user_task.uid AND sys_user_goal.gid=cfg_task_goal.id AND cfg_task_goal.tid=sys_user_task.tid)" );
			foreach ( $tasks as $task ) {
				sql_query ( "insert into sys_user_task (uid,tid,state) values ($uid,'$task[tid]',0) on duplicate key update state=0" );
			}
		
			/*
			$tasks = sql_fetch_rows ( "SELECT DISTINCT tid FROM sys_user_task a join cfg_task b on a.tid=b.id WHERE a.uid='$battlefieldinfo[createuid]' AND a.tid>=60000 AND a.tid<=70000 and (b.pretid=0 or b.pretid=-1)" );
			$taskList = sql_fetch_one_cell ( "SELECT GROUP_CONCAT(DISTINCT tid) FROM sys_user_task WHERE uid='$battlefieldinfo[createuid]' AND tid>=60000 AND tid<=70000 " );
			$goals = sql_fetch_rows("SELECT gid FROM sys_user_goal a join cfg_task_goal b on a.gid=b.id WHERE a.uid='$battlefieldinfo[createuid]' AND b.tid in ($taskList)");
			foreach ( $tasks as $task ) {
				sql_query ( "insert into sys_user_task (uid,tid,state) values ($uid,'$task[tid]',0) on duplicate key update state=0" );
				
			}
			foreach ( $goals as $goal ) {
				sql_query ( "insert into sys_user_goal (uid,gid,currentcount) values ($uid,'$goal[gid]',0) on duplicate key update currentcount=0" );
			}
			*/
		} else if($bid==8001) {
		        $tasks = explode ( ",","60800,60815,60816" );
                foreach ( $tasks as $task ) {
                    //error_log("insert into sys_user_task (uid,tid,state) values ($uid,'$task',0) on duplicate key update state=0");
                     
                   sql_query("insert into sys_user_task (uid,tid,state) values ($uid,'$task',0) on duplicate key update state=0");
                }
		}
		
		sql_query ( "delete from sys_battle_invite where id='$inviteinfo[id]' " );
		playerAttendBattle ( $battlefieldid, $unionid, $inviteinfo ['touid'], $inviteinfo ['toname'], $fieldname );
		$currentbattleinfo = firstGetUserBattleInfo ( $uid );
		
		$cityinfo = sql_fetch_rows ( "select * from sys_battle_city where battlefieldid='$battlefieldid' " );
		$cityinfo = setFlags ( $uid, $unionid, $cityinfo );
		
		$ret = array ();
		$ret [] = $currentbattleinfo;
		$ret [] = $cityinfo;
		$ret [] = getBattleStartCityInfo ( $bid, $unionid );
		sql_query ( "insert into mem_user_schedule (`uid`,`today_war_count`) values ('$uid',1) on duplicate key update `today_war_count`=today_war_count+1" );
		unlockUser ( $uid );
		return $ret;
	}
	unlockUser ( $uid );
	throw new Exception ( $GLOBALS ['battle'] ['join_fail'] );

}

function joinToBattle9001($uid, $param) {
	$inviteid = intval ( array_shift ( $param ) );
	$inviteinfo = sql_fetch_one ( "select id, battlefieldid,toname,touid from sys_battle_invite where id='$inviteid' and touid='$uid'" );
	
	if (empty ( $inviteinfo )) {
		throw new Exception ( $GLOBALS ['battle'] ['no_such_invite'] );
	}
	
	if (sql_check ( "select * from sys_user_battle_state where state=0 and uid='$uid'" )) {
		throw new Exception ( $GLOBALS ['battle'] ['join_user_already_in_battle'] );
	}
	if(defined("BATTLE_NET_ENABLE") && BATTLE_NET_ENABLE){
		$waitingBid = sql_fetch_one_cell("select bid from sys_battlenet_waiting_queue where uid='$uid'");
		if ($waitingBid>0) {
			throw new Exception ( $GLOBALS['battle']['already_in_battlenet'] );
		}
//		$battlenetinfo = sendRemoteRequest ( $uid, "getUserBattleState" );
//		if ($battlenetinfo [0] == 2) {
//			throw new Exception ( $GLOBALS['battle']['already_in_battlenet'] );
//		}
	}
	$params = array();
	$params[] = 9001;
	$params[] = $inviteinfo['battlefieldid'];
	sendRemote9001Request($uid,"addUser",$params);
    
	sql_query ( "delete from sys_battle_invite where id='$inviteinfo[id]' " );
}

function joinToPVPBattle($uid, $param) {
	$bid = intval ( array_shift ( $param ) );
	$battlefieldid = intval ( array_shift ( $param ) );
	$unionid = intval ( array_shift ( $param ) );
	$unioninfo = sql_fetch_one ( "select * from cfg_battle_union where unionid=$unionid" );
	if (sql_check ( "select * from sys_user_battle_state where state=0 and uid='$uid'" )) {
		throw new Exception ( $GLOBALS ['battle'] ['join_user_already_in_battle'] );
	}
	
	if(defined("BATTLE_NET_ENABLE") && BATTLE_NET_ENABLE){
		if ($bid == 6001 && sql_check ( "select * from sys_user_battle_field where id='$battlefieldid' and state=0" )) {
			//逐鹿中原副本开始以后不能加入
			throw new Exception ( $GLOBALS ['battle'] ['alreay_open'] );
		}
//		$battlenetinfo = sendRemoteRequest ( $uid, "getUserBattleState" );
//		if ($battlenetinfo [0] == 2) {
//			throw new Exception ( $GLOBALS['battle']['already_in_battlenet'] );
//		}
		if(sql_check("select 1 from sys_battlenet_waiting_queue where uid=$uid")){
			throw new Exception ( $GLOBALS['battle']['already_in_battlenet'] );
		}
    }
	
	$userinfo = sql_fetch_one ( "select * from sys_user where uid='$uid' " );
	$currenthonour = $userinfo ["honour"];
	
	if ($currenthonour < 0) {
		throw new Exception ( $GLOBALS ['battle'] ['honour_invalid'] );
	}
	
	$battle = sql_fetch_one ( "select * from cfg_battle_field where id=$bid and state<>0" );
	$maxpeople = $battle ["maxpeople"];
	
	if ($battlefieldid == 0) {
		$battlefieldid = intval ( sql_fetch_one_cell ( "select battlefieldid  from sys_user_battle_state where unionid= $unionid and  bid = $bid group by battlefieldid having count(1)<$maxpeople order by count(1) limit 1" ) );
		if (empty ( $battlefieldid ) || $battlefieldid == 0)
			$battlefieldid = intval ( sql_fetch_one_cell ( "select id  from sys_user_battle_field where bid = $bid limit 1" ) );
	}
	$battlefieldinfo = sql_fetch_one ( "select * from sys_user_battle_field where id='$battlefieldid'" );
	if (empty ( $battlefieldinfo )) {
		throw new Exception ( $GLOBALS ['battle'] ['no_battle_field'] );
	}
	if ($battlefieldinfo ['state'] == 1 || $battlefieldinfo ['state'] == 3) {
		throw new Exception ( $GLOBALS ['battle'] ['no_battle_field_froze'] );
	}
	
	$curCount = intval ( sql_fetch_one_cell ( "select count(1) from sys_user_battle_state where state=0 and battlefieldid=$battlefieldid and unionid= $unionid " ) );
	if ($curCount >= intval ( $maxpeople ))
		throw new Exception ( $GLOBALS ['battle'] ['max_people'] );
		
	/*//现有开启战场等级
	$isOpenLevel=false;
	$openLevel=$GLOBALS['battle']['open_level'];
	for($i=0;$i<count($openLevel);$i++){
		if($openLevel[$i]==$battlefieldinfo["level"]){
			$isOpenLevel=true;
			break;
		}
	}
	if(!$isOpenLevel) throw new Exception($GLOBALS['battle']['not_open_level']);
	*/
	
	if ($bid == 4001 && $battlefieldinfo ["progress"] != 0) {
		//董卓副本只能在第一阶段才能加入
		throw new Exception ( $GLOBALS ['battle'] ['can_not_join'] );
	}
	checkUserQualityForJoinBattle ( $uid, $bid );
	if (! lockUser ( $uid ))
		throw new Exception ( $GLOBALS ['pacifyPeople'] ['server_busy'] );
	if ($bid == 6001 && $unionid == 25) {
		//逐鹿中原副本，把用户自己的uid作为uid
		$unionid = $uid;
	}
	$result = sql_query ( "insert into sys_user_battle_state (uid,bid,battlefieldid,unionid,level) values($uid,$bid,$battlefieldid,$unionid,'$battlefieldinfo[level]') on duplicate key update  battlefieldid=$battlefieldid,bid=$bid,unionid=$unionid,level='$battlefieldinfo[level]'" );
	if ($result) {
		/*$taskgroup = sql_fetch_one_cell("select taskgroup from cfg_battle_union where  unionid = $unionid");
		 if($taskgroup){ //加任务
		 $groupArray  = explode(",", $taskgroup);
		 if ($groupArray){
		 foreach ($groupArray as $group) {
		 if ($group <=10) continue;
		 $tasks=sql_fetch_rows("select id from cfg_task where `group` in (60000,60001,60002,60003,60004) and pretid=-1" );
		 foreach($tasks as $task){
		 sql_query("insert into sys_user_task (uid,tid,state) values ($uid,'$task[id]',0) on duplicate key update state=0");
		 }
		 }
		 }
		 }*/
		deleteOldBattleTasks ( $uid );
		if ($bid == 2001) {
			if ($unionid == 3) {
				//添加袁绍一方任务
				$tasks = sql_fetch_rows ( "select id from cfg_task where `group` in (60005,60006,60007,60008,60009,600010) and pretid=-1" );
				foreach ( $tasks as $task ) {
					sql_query ( "insert into sys_user_task (uid,tid,state) values ($uid,'$task[id]',0) on duplicate key update state=0" );
				}
			} else if ($unionid == 4) {
				//添加曹操一方任务
				$tasks = sql_fetch_rows ( "select id from cfg_task where `group` in (60011,60012,60013,60014,60015,60016,60017,60018) and pretid=-1" );
				foreach ( $tasks as $task ) {
					sql_query ( "insert into sys_user_task (uid,tid,state) values ($uid,'$task[id]',0) on duplicate key update state=0" );
				}
			}
		} else if ($bid == 4001) {
			//只能在副本第一阶段的时候加入
			

			$tasks = array (60301, 60302, 60303, 60307, 60308, 60309, 60310, 60311, 60312 );
			foreach ( $tasks as $task ) {
				sql_query ( "insert into sys_user_task (uid,tid,state) values ($uid,'$task',0) on duplicate key update state=0" );
			}
			sql_query ( "insert ignore into mem_battle_attack_lock(battlefieldid,cid,uid,msg) values($battlefieldid,375,$uid,'没有拿到泗水先锋令之前不能攻击汜水关')" );
			sql_query ( "insert ignore into mem_battle_attack_lock(battlefieldid,cid,uid,msg) values($battlefieldid,343,$uid,'没有拿到虎牢先锋令之前不能攻击虎牢关')" );
		
		} else {
			$tasks = explode ( ",", $unioninfo ["init_tasks"] );
			foreach ( $tasks as $task ) {
				sql_query ( "insert into sys_user_task (uid,tid,state) values ($uid,'$task',0) on duplicate key update state=0" );
			}
		}
		
		$currentbattleinfo = firstGetUserBattleInfo ( $uid );
		$cityinfo = sql_fetch_rows ( "select * from sys_battle_city where battlefieldid='$battlefieldid' " );
		$cityinfo = setFlags ( $uid, $unionid, $cityinfo );
		$fieldname = $GLOBALS ['battle'] ['union_name'] [$unionid];
		playerAttendBattle ( $battlefieldid, $unionid, $uid, $userinfo ['name'], $fieldname );
		$ret = array ();
		$ret [] = $currentbattleinfo;
		$ret [] = $cityinfo;
		$ret [] = getBattleStartCityInfo ( $bid, $unionid );
		//load血条
		//官渡之战
		$ret [] = sql_fetch_rows ( "select * from sys_battle_winpoint where battlefieldid='$battlefieldid'" );
		
		$id = sql_fetch_one_cell ( "select id from cfg_task_goal where  tid=285" );
		sql_query ( "insert into sys_user_goal(`uid`,`gid`) values ('$uid','$id') on duplicate key update gid='$id'" );
		unlockUser ( $uid );
		return $ret;
	}
	unlockUser ( $uid );
	throw new Exception ( $GLOBALS ['battle'] ['join_fail'] );

}

function getBattleGroups($uid, $param) {
	$type = intval ( array_shift ( $param ) );
	$ret = array ();
	$ret [] = sql_fetch_rows ( "select * from cfg_battle_field where type='$type'" );
	return ret;
}

function getSoldierPeople($soldiers) {
	//$SOLDIER_PEOPLE =array(0,1,1,1,1,1,2,3,6,4,5,10,8);
	$soldiersarray = explode ( ",", $soldiers );
	$count = array_shift ( $soldiersarray );
	$people = 0;
	for($i = 0; $i < $count; $i ++) {
		$sid = array_shift ( $soldiersarray );
		$people += array_shift ( $soldiersarray );
	}
	return $people;
}

/**
 * 给部队增加援军 并且记录
 *
 * @param unknown_type $troopid
 * @param unknown_type $orisoldiers
 * @param unknown_type $sid
 * @param unknown_type $count
 * @return unknown
 */
function enhanceArmy($troopid, $orisoldiers, $sid, $count, $cid, $hid,$bid=0) {
	$orisoldiersarray = explode ( ",", $orisoldiers );
	$orisoldiertypecount = array_shift ( $orisoldiersarray );
	//$people = 0;
	$contain = false;
	$newsoldiers = "";
	/*if($orisoldiertypecount>12){
		return false;
	}else if($orisoldiertypecount==12){
		
	}*/
	$zerosid="";
	$deletecount=0;
	for($i = 0; $i < $orisoldiertypecount; $i ++) {
		$orisid = array_shift ( $orisoldiersarray );
		$oricount = array_shift ( $orisoldiersarray );
		if ($orisid == $sid) {
			$oricount += $count;
			$contain = true;
		}
		if($oricount<=0){
			$zerosid.=$orisid.",";
			$deletecount+=1;
		}else {
			$newsoldiers = $newsoldiers . "," . $orisid . "," . $oricount;
		}
	}
	$orisoldiertypecount-=$deletecount;
	if ($contain == false) {
		$orisoldiertypecount ++;
		$newsoldiers = $newsoldiers . "," . $sid . "," . $count;
	}
	if($orisoldiertypecount>12){
		throw new Exception($orisoldiertypecount.$GLOBALS['depluder']['too_many_sid']);
	}
	/*if($bid==5001&&$orisoldiertypecount>=11){
		throw new Exception($orisoldiertypecount.$GLOBALS['depluder']['too_many_sid']);
	}*/
	/*
	*/
	
	$newsoldiers = $orisoldiertypecount . $newsoldiers;
	//重算行军速度
	$newPathTime = reGeneratePathTime ( $cid, $hid, $newsoldiers );
	$result = sql_query ( "update sys_troops set soldiers='$newsoldiers',pathtime='$newPathTime' where id='$troopid'" );
	if ($result) {
		sql_query("delete from sys_battle_reinforce where troopid='$troopid' and `count`<=0");
		sql_query ( "insert into sys_battle_reinforce (troopid,sid,count) values ($troopid,$sid,$count) on duplicate key update count=count+$count" );
		return $newsoldiers;
	}
	return false;
}

/**
 * 判断战场结束时候阵营是否胜利
 *
 * @param unknown_type $uid
 * @param unknown_type $bid
 */
function checkBattleResult($bid, $battlefieldid) {
	if ($bid == 1001) {
		return check1001 ( $battlefieldid );
	}
	return 0;
}

/**
 * 检查黄巾之乱的战场结果,条件：判断战场里是否还有张角三兄弟的军队
 *
 * @param unknown_type $uid
 */
function check1001($battlefieldid) {
	$result = sql_fetch_rows ( "select * from sys_troops where uid=0 and battlefieldid='$battlefieldid' and hid in(1001,1002,1005) " );
	if (empty ( $result ))
		return 1;
	return 2;
}

function getSoldierMap($orisoldiers) {
	if ($orisoldiers == "") {
		$orisoldiers = "0,";
	}
	$reinforcearray = explode ( ",", $orisoldiers );
	$reinforcecount = array_shift ( $reinforcearray );
	$reinforcemap = array ();
	
	for($i = 0; $i < $reinforcecount; $i ++) {
		$sid = array_shift ( $reinforcearray );
		$count = array_shift ( $reinforcearray );
		$sidindex = $sid . "";
		$reinforcemap [$sid] = $count;
	}
	return $reinforcemap;
}

function getSoldierString($soldiersmap) {
	
	$soldiersmapcount = count ( $soldiersmap );
	$newsoldierscount = 0;
	$newsoldiers = "";
	foreach ( $soldiersmap as $sid => $count ) {
		//$sid=array_shift($soldier);
		//$count=array_shift($soldier);
		if ($count > 0) {
			$newsoldiers = $newsoldiers . "," . $sid . "," . $count;
			$newsoldierscount ++;
		}
	}
	if ($newsoldierscount > 0) {
		$newsoldiers = $newsoldierscount . $newsoldiers;
	}
	return $newsoldiers;
}
/**
 *
 *
 * @param unknown_type $uid
 * @param unknown_type $unionid
 * @param unknown_type $troops
 * @return unknown
 */
function setFlags($uid, $unionid, $cityinfos) {
	//默认白旗帜，无人
	$resultcityinfo = array ();
	$flagcharmap = sql_fetch_simple_map ( "select unionid,flag from cfg_battle_union", "unionid", "flag" );
	foreach ( $cityinfos as $cityinfo ) {
		$cityunionid = $cityinfo ['unionid'];
		$cityuid = $cityinfo ['uid'];
		if ($cityunionid >= 1) {
			if ($cityunionid > 1000) {
				$flagchar = sql_fetch_one_cell ( "select flagchar from sys_user where uid=$cityunionid" );
			} else {
				$flagchar = $flagcharmap [$cityunionid];
			}
		}
		if ($cityunionid == - 1) {
			//空据点，白旗
			$cityinfo ['flag'] = 5;
			$cityinfo ['flagchar'] = "";
		} else if ($cityunionid == 0) {
			//争夺中,玫瑰色
			$cityinfo ['flag'] = 3;
			$cityinfo ['flagchar'] = "";
		} else if ($cityunionid == $unionid) {
			//同一阵营
			

			$cityinfo ['flagchar'] = $flagchar;
			if (! $cityinfo ['hasuser']) {
				//npc城
				$cityinfo ['flag'] = 2;
			} else {
				//我方据点
				$cityinfo ['flag'] = 1;
			
			}
		} else {
			//敌人阵营
			

			$cityinfo ['flagchar'] = $flagchar;
			
			if (! $cityinfo ['hasuser']) {
				$cityinfo ['flag'] = 0;
			
			} else {
				//敌方玩家据点
				$cityinfo ['flag'] = 4;
			
			}
		}
		$resultcityinfo [] = $cityinfo;
	}
	
	return $resultcityinfo;
}

function getBattleHonourStat($uid, $param) {
	$battleinfo = firstGetUserBattleInfo ( $uid );
	if ($battleinfo ["type"] != 1) {
		//暂时不支持剧情战场的贡献统计
		return;
	}
	$battlefieldid = $battleinfo ["battlefieldid"];
	$ret = array ();
	if ($battleinfo ["bid"] != 6001) {
		//目前的官渡，董卓，徐州最多两个阵营
		$avgHonourData = sql_fetch_map ( "SELECT a.unionid,FLOOR(AVG(honour)) AS avgHonour,b.name FROM sys_user_battle_state a LEFT JOIN cfg_battle_union b ON a.unionid=b.unionid where battlefieldid=$battlefieldid GROUP BY unionid", "unionid" );
		$datas = sql_fetch_complex_map ( "SELECT b.uid AS uid,a.unionid,b.name AS name,a.honour FROM sys_user_battle_state a,sys_user b WHERE a.uid=b.uid AND battlefieldid=$battlefieldid ORDER BY a.honour DESC,a.jointime ASC", "unionid" );
	} else {
		//逐鹿中原可能有N个阵营，就糅在一起算，阵营名字就叫“所有”，每个玩家的贡献度都是10
		$avgHonourData = sql_fetch_map ( "SELECT 0 as unionid, FLOOR(AVG(honour)) AS avgHonour  FROM sys_user_battle_state  WHERE battlefieldid=$battlefieldid", "unionid" );
		$avgHonourData ["0"] ["name"] = $GLOBALS ['battle'] ['all_unions'];
		$datas = sql_fetch_complex_map ( "SELECT b.uid AS uid,0 AS unionid,b.name AS name,a.honour FROM sys_user_battle_state a,sys_user b WHERE a.uid=b.uid AND battlefieldid=$battlefieldid ORDER BY a.honour DESC,a.jointime ASC", "unionid" );
	}
	foreach ( $datas as $unionid => &$uniondata ) {
		$unionAvgHonour = $avgHonourData [$unionid] ["avgHonour"];
		foreach ( $uniondata as &$userdata ) {
			if ($unionAvgHonour == 0) {
				$userdata ["contribute"] = 0;
			} else {
				$userdata ["contribute"] = floor ( 10 * $userdata ["honour"] / $unionAvgHonour );
			}
		}
	}
	$ret [] = $avgHonourData;
	$ret [] = $datas;
	return $ret;
}
/**
 * 读取战场成员列表
 *
 * @param unknown_type $uid
 * @param unknown_type $param
 */
function getBattleFieldUsers($uid, $param) {
	$battlefiledid = intval ( array_shift ( $param ) );
	
	$battleinfo = firstGetUserBattleInfo ( $uid );
	
	$ret = array ();
	
	$iscreator = false;
	if ($uid == $battleinfo ['createuid']) {
		$iscreator = true;
	}
	
	//战场中的用户
	$userin = sql_fetch_rows ( "select s.uid,u.honour,u.name, c.name as camp from sys_user_battle_state s left join sys_user u on s.uid=u.uid
	left join cfg_battle_union c on c.bid=s.bid and c.unionid=s.unionid  where s.state=0 and s.battlefieldid='$battleinfo[battlefieldid]'" );
	$userininfo = array ();
	foreach ( $userin as $oneuser ) {
		$herocount = sql_fetch_one_cell ( "select count(*) from sys_troops where  uid='$oneuser[uid]' and battlefieldid='$battleinfo[battlefieldid]' " );
		
		//阵营
		//$oneuser['camp']
		

		$oneuser ['herocount'] = $herocount;
		$oneuser ['state'] = $GLOBALS ['battle'] ['state_in'];
		$oneuser ['cancel'] = false;
		if (empty ( $oneuser ['honour'] )) {
			$oneuser ['honour'] = 0;
		}
		$userininfo [] = $oneuser;
	}
	
	//已经参与的人数
	

	$incount = count ( $userininfo );
	//如果是创建者
	$userinvite = sql_fetch_rows ( "select i.id,i.toname as name,t.honour from sys_battle_invite i left join sys_user t on i.touid=t.uid where battlefieldid='$battleinfo[battlefieldid]'" );
	$userinviteinfo = array ();
	foreach ( $userinvite as $oneuser ) {
		$oneuser ['herocount'] = 0;
		$oneuser ['state'] = $GLOBALS ['battle'] ['state_invite'];
		if ($iscreator)
			$oneuser ['cancel'] = true;
		else
			$oneuser ['cancel'] = false;
		if (empty ( $oneuser ['honour'] )) {
			$oneuser ['honour'] = 0;
		}
		$userinviteinfo [] = $oneuser;
	
	}
	
	$userinfo = array_merge ( $userininfo, $userinviteinfo );
	$ret [] = $userinfo;
	$ret [] = $incount;
	$ret [] = $iscreator;
	return $ret;
}

function inviteBattleUser($uid, $param) {
	$battlefieldid = intval ( array_shift ( $param ) );
	$invitename = array_shift ( $param );
	$invitename = addslashes($invitename);
	$myname = array_shift ( $param );
	$myname = addslashes($myname);
	$battleinfo = sql_fetch_one ( "select * from sys_user_battle_field where id='$battlefieldid' " );
	if (empty ( $battleinfo )) {
		//你已经不在战场中	
		throw new Exception ( $GLOBALS ['battle'] ['user_not_in_battle'] );
	}
	if ($battleinfo ["state"] == 1) {
		throw new Exception ( $GLOBALS ['battle'] ['battle_froze'] );
	}
	if ($uid != $battleinfo ['createuid']) {
		throw new Exception ( $GLOBALS ['battle'] ['invite_not_creator'] );
	}
	
	$touser = sql_fetch_one ( "select uid,honour from sys_user where name='$invitename' " );
	
	if (empty ( $touser )) {
		throw new Exception ( $GLOBALS ['battle'] ['invite_user_not_exist'] );
	}
	$touserid = $touser ['uid'];

	
	$honour = $touser ['honour'];
	if ($honour < 0) {
		throw new Exception ( $GLOBALS ['battle'] ['invite_not_enough_honour'] );
	}
	
	if (sql_check ( "select id from sys_battle_invite where battlefieldid='$battleinfo[id]' and touid='$touserid' " )) {
		throw new Exception ( $GLOBALS ['battle'] ['invite_user_already'] );
	}
	
	$count = sql_fetch_one_cell ( "select count(*) from sys_user_battle_state where state=0 and battlefieldid=$battleinfo[id]" );
	
	if ($count >= $battleinfo ['maxpeople']) {
		throw new Exception ( $GLOBALS ['battle'] ['invite_max_people'] );
	}
	
	$fieldname = $GLOBALS ['battle'] ['name'] [$battleinfo ['bid']];
	$resultid = sql_insert ( "insert into sys_battle_invite(fromuid ,battlename,battlefieldid ,level,time ,touid ,fromname,toname )
	values ($uid,'$fieldname','$battleinfo[id]','$battleinfo[level]',unix_timestamp(),$touserid,'$myname','$invitename')" );
	$ret = array ();
	if ($resultid) {
		$ret [] = 1;
		$ret [] = $GLOBALS ['battle'] ['invite_user_suc'];
		$ret [] = $count;
		$ret [] = array ("fromuid" => $uid, "id" => $resultid, "touid" => $touserid, "name" => $invitename, "herocount" => 0, "honour" => $honour, "cancel" => true, "state" => $GLOBALS ['battle'] ['state_invite'] );
		
		$id = sql_fetch_one_cell ( "select id from cfg_task_goal where  tid=283" );
		sql_query ( "insert into sys_user_goal(`uid`,`gid`) values ('$uid','$id') on duplicate key update gid='$id'" );
	} else {
		$ret [] = 0;
		$ret [] = $GLOBALS ['battle'] ['invite_user_fail'];
		$ret [] = $count;
	
	}
	
	return $ret;
}

function inviteBattleUser9001($uid, $param) {
//function inviteUser($uid, $param) {
	$roomId = intval ( array_shift ( $param ) );
	$invitename = array_shift ( $param );
	$myname =sql_fetch_one_cell("select name from sys_user where uid=$uid");
	
	$params = array();
	$params[] = $roomId;
	$roominfo = sendRemote9001Request($uid, "getMenghuoRemoteRoom", $params);
	if (empty ( $roominfo )) {
		//你已经不在战场中	
		throw new Exception ( $GLOBALS ['battle'] ['user_not_in_room'] );
	}
	if ($uid != $roominfo ['from_uid']) {
		throw new Exception ( $GLOBALS ['battle'] ['invite_not_creator'] );
	}
	
	$touser = sql_fetch_one ( "select uid,nobility,honour from sys_user where name='$invitename' " );
	
	if (empty ( $touser )) {
		throw new Exception ( $GLOBALS ['battle'] ['invite_user_not_exist'] );
	}
	$touserid = $touser ['uid'];
	if($touserid == $uid) {
		throw new Exception ( $GLOBALS ['battle'] ['invite_user_self'] );
	}
	$honour = $touser ['honour'];
	if ($honour < 0) {
		throw new Exception ($GLOBALS['battle']['invite_not_enough_honour']);
	}
	
	$nobility = $touser ['nobility'];
	checkUserDemand9001($touserid, $roominfo['level'], $nobility);
	
	if (sql_check ( "select id from sys_battle_invite where battlefieldid='$roominfo[id]' and touid='$touserid' " )) { //用roomid替代battlefieldid
		throw new Exception ( $GLOBALS ['battle'] ['invite_user_already'] );
	}
	
	//$count = sql_fetch_one_cell ( "select count(*) from sys_user_battle_state where state=0 and battlefieldid=$roominfo[id]" );
	
	if ($roominfo['count'] >= $roominfo ['ceil']) {
		throw new Exception ( $GLOBALS ['battle'] ['invite_max_people'] );
	}
	
	$fieldname = $GLOBALS ['battle'] ['name'] [9001];
	$roomlevel=$roominfo[level]+1;
	$insql= "insert into sys_battle_invite(fromuid ,battlename,battlefieldid ,level,time ,touid ,fromname,toname )
	values ($uid,'$fieldname','$roominfo[id]','$roomlevel',unix_timestamp(),$touserid,'$myname','$invitename')" ;
	$resultid = sql_insert ($insql);
	$ret = array ();
	if ($resultid) {
		$ret [] = 1;
		$ret [] = $GLOBALS ['battle'] ['invite_user_suc'];
		$ret [] = $roominfo['count'];
		$ret [] = array ("fromuid" => $uid, "id" => $resultid, "touid" => $touserid, "name" => $invitename, "herocount" => 0, "honour" => $honour, "cancel" => true, "state" => $GLOBALS ['battle'] ['state_invite'] );
		
		$id = sql_fetch_one_cell ( "select id from cfg_task_goal where  tid=283" );
		sql_query ( "insert into sys_user_goal(`uid`,`gid`) values ('$uid','$id') on duplicate key update gid='$id'" );
	} else {
		$ret [] = 0;
		$ret [] = $GLOBALS ['battle'] ['invite_user_fail'];
		$ret [] = $roominfo['count'];
	
	}
	
	return $ret;
}

function checkUserDemand9001($uid, $level,$nobility) {
	if ($level==0) {
		$demandNobility = 1;
	}else if ($level==1) {
		$demandNobility = 4;
		$achivement_id=70022;
	}else if ($level==2) {
		$demandNobility = 5;
		$achivement_id=70023;
	}else{
		$demandNobility = 6;
		$achivement_id=70024;
	}
	if($nobility<$demandNobility){
		throw new Exception ( $GLOBALS ['battle'] ['invite_not_enough_level'] );
	}
	if ($level>0 && !sql_check("select * from sys_user_achivement where uid='$uid' and achivement_id='$achivement_id'")) {
//		$params = array();
//		$params[] = $achivement_id;			
//		if (sendRemoteRequest($uid,"checkUserAchivement",$params)) {//如果这个成就完成了，就拿到跨服上
//			sql_query("INSERT INTO sys_user_achivement(uid,achivement_id,TIME) VALUES('$uid','$achivement_id',UNIX_TIMESTAMP())");
//		}else{
			throw new Exception ( $GLOBALS ['battle'] ['invite_not_enough_level'] );		
//		}
	}
}

function ignoreBattle($uid, $param) {
	$inviteid = intval ( array_shift ( $param ) );
	sql_query ( "delete from  sys_battle_invite where id='$inviteid' " );
	$ret = array ();
	$ret [] = sql_fetch_rows ( "select * from sys_battle_invite where touid='$uid' and state=0 order by time desc" );
	return $ret;
}

function cancelBattleInvite($uid, $param) {
	$inviteid = intval ( array_shift ( $param ) );
	$invite = sql_fetch_one ( "select * from sys_battle_invite where id=$inviteid and fromuid=$uid" );
	if (empty ( $invite )) {
		throw new Exception ( $GLOBALS ['battle'] ['invite_not_exist'] );
	}
	
	$ret = array ();
	
	$result = sql_query ( "delete from sys_battle_invite where id=$inviteid" );
	if ($result) {
		$ret [] = $GLOBALS ['battle'] ['cancel_invite_suc'];
		$ret [] = $inviteid;
	} else {
		$ret [] = $GLOBALS ['battle'] ['cancel_invite_suc'];
		$ret [] = 0;
	}
	return $ret;
}

///jun.zhao
/**
 * 玩家加入战场
 * @param $battleid
 * @param $unionid
 * @param $nick
 * @return unknown_type
 */
function playerAttendBattle($battleid, $unionid, $uid, $nick, $camp_name) {
	$content = "";
	if ($camp_name == "") {
		$content = sprintf ( $GLOBALS ['battle'] ['login_1'], $nick );
	} else {
		$content = sprintf ( $GLOBALS ['battle'] ['login'], $camp_name, $nick );
	}
	$content = addslashes ( $content );
	#echo "insert into `mem_war_buf` (`battleid`, `unionid`,`uid`,`nick`, `state`,`updatetime`) values('$battleid','$unionid','$uid','$nick', '1', unix_timestamp())";
	$error = sql_insert ( "insert into `mem_war_buf` (`battleid`, `unionid`,`uid`,`nick`, `state`, `updatetime`) values('$battleid','$unionid','$uid','$nick', 1, unix_timestamp())" );
	if ($error == 0)
		return 0;
	$error = sql_insert ( "insert into `mem_war_event` (`battleid`, `unionid`,`type`,`content`,`evttime`) values ('$battleid', '$unionid', 2, '$content',unix_timestamp())" );
	//sql_query("INSERT INTO `log_battle_news` (`battleid`, `unionid`, `content`, `log_time`)  VALUES ('$battleid', '$unionid', '$content', unix_timestamp())");
	if ($error == 0) {
		return 0;
	}
	return 1;
}
function playerExitBattle($battleid, $unionid, $uid, $nick, $camp_name) {
	#$content = "玩家:".$nick."退出了战场";
	$content = sprintf ( $GLOBALS ['battle'] ['logout'], $camp_name, $nick );
	$content = addslashes ( $content );
	#echo "insert into `mem_war_buf` (`battleid`, `unionid`,`uid`,`nick`,`updatetime`) values('$battleid','$unionid','$uid','$nick', '0', unix_timestamp())";
	$error = sql_insert ( "insert into `mem_war_buf` (`battleid`, `unionid`,`uid`,`nick`, `state`, `updatetime`) values('$battleid','$unionid','$uid','$nick', 0, unix_timestamp())" );
	if ($error == 0)
		return 0;
	$error = sql_insert ( "insert into `mem_war_event` (`battleid`, `unionid`,`type`,`content`,`evttime`) values ('$battleid', '$unionid', 2, '$content',unix_timestamp())" );
	//sql_query("INSERT INTO `log_battle_news` (`battleid`, `unionid`, `content`, `log_time`)  VALUES ('$battleid', '$unionid', '$content', unix_timestamp())");
	if ($error == 0) {
		return 0;
	}
	return 1;
}

/*function playTroopLeave($battleid, $unionid, $camp_name, $battlePlace,$heroname)
 {
 $content =  sprintf($GLOBALS['battle']['troop_leave'], $camp_name,$heroname, $battlePlace);
 $sqll="insert into `mem_war_event` (`battleid`, `unionid`,`type`,`content`,`evttime`) values ('$battleid ', '$unionid', 2, '$content',unix_timestamp())";
 sql_query($sqll);
 sql_query("INSERT INTO `log_battle_news` (`battleid`, `unionid`, `content`, `log_time`)  VALUES ('$battleid', '$unionid', '$content', unix_timestamp())");

 }*/

function getBattleNews($uid, $param) {
	$battleid = intval(array_shift ( $param ));
	$unionid = intval(array_shift ( $param ));
	$page = intval(array_shift ( $param ));
	$pageCount = intval(array_shift ( $param ));
	$start = intval ( $page ) * intval ( $pageCount );
	$ret = Array ();
	$ret [] = sql_fetch_one_cell ( "select count(*) from log_battle_news where battleid='$battleid'" );
	$ret [] = sql_fetch_rows ( "select * from log_battle_news where battleid='$battleid' order by log_time desc LIMIT $start,$pageCount" );
	return $ret;
}

function getBattleInfor($uid, $param) {
	$bid = intval(array_shift ( $param ));
	$battleid = intval(array_shift ( $param ));
	$unionid = intval(array_shift ( $param ));
	#$ret = array();
	$info1 = sql_fetch_one ( "select name, minpeople, maxpeople, maxlevel, content,winCondition,loseCondition from cfg_battle_field where id ='$bid'" );
	if (empty ( $info1 )) {
		throw new Exception ( $GLOBALS ['battle'] ['no_battle_infor'] );
	}
	#	$ret[] = $info;
	

	$info2 = sql_fetch_one ( "select count(*) as total  from sys_user_battle_state where state=0 and battlefieldid=$battleid" );
	if (empty ( $info2 )) {
		throw new Exception ( $GLOBALS ['battle'] ['no_battle_infor'] );
	}
	
	$info3 = sql_fetch_one ( "select *  from sys_user_battle_field where state=0 and id=$battleid" );
	if (empty ( $info3 )) {
		throw new Exception ( $GLOBALS ['battle'] ['no_battle_infor'] );
	}
	$ret [] = $info1 ['name'];
	$ret [] = $info1 ['minpeople'];
	$ret [] = $info1 ['maxpeople'];
	$ret [] = $info3 ["level"];
	$ret [] = $info1 ["content"];
	$ret [] = $info2 ['total'];
	$ret [] = $info1 ["winCondition"];
	$ret [] = $info1 ["loseCondition"];
	return $ret;
}

function getBattleStartCityInfo($bid, $unionid) {
	if ($bid == "" || $unionid == "") {
		return;
		throw new Exception ( $GLOBALS['battle']['not_true']);
	}
	if ($bid == 6001) {
		//逐鹿中原副本能够出征的城市是随机分配的，第一次分配以后不再更改
		//这是unionid其实就是用户的uid
		$startcid = sql_fetch_one_cell ( "select startcid from sys_user_battle_state where state=0 and uid=$unionid" );
		if ($startcid == 0) {
			//还没有分配，从现有城池中分配，但是洛阳，长安，江陵，昌邑，晋阳这几个城池不能被分配到
			$ret = sql_fetch_rows ( "SELECT xy,`name` FROM cfg_battle_city WHERE bid=6001 AND xy NOT IN (407,374,97,410,285) ORDER BY RAND() LIMIT 1" );
			$xy = $ret [0] ["xy"];
			sql_query ( "update sys_user_battle_state set startcid=$xy where state=0 and uid=$unionid" );
		} else {
			$ret = sql_fetch_rows ( "SELECT xy,`name` FROM cfg_battle_city WHERE bid=6001 AND xy=$startcid" );
		}
		return $ret;
	} else {
		return sql_fetch_rows ( "select * from cfg_battle_start_city where bid=$bid and unionid=$unionid order by needhonour asc" );
	}
}

/**
 * 军队离开某个据点以后重新判定据点的归属
 *
 * @param unknown_type $cid
 * @return unknown
 */

function resetBattleFieldUid($cid) {
	
	$result = array ();
	$result ["oriuid"] = 0;
	$result ["oriunionid"] = 0;
	$result ["uid"] = 0;
	$result ["unionid"] = 0;
	
	//据点里有没有wa
	$hasuser = 0;
	
	$uid = - 1;
	$unionid = - 1;
	$sameUid = true;
	
	//在这个据点里战斗的部队或者驻守的部队 都算作这个据点的部队
	$troops = sql_fetch_rows ( "select * from sys_troops where cid=$cid and ((state=4 or state=3) or targetcid=$cid)" );
	
	$oricidinfo = sql_fetch_one ( "select uid,unionid from sys_battle_city where cid=$cid" );
	if (empty ( $oricidinfo )) {
		return $result;
	}
	$result ["oriuid"] = $oricidinfo ["uid"];
	$result ["oriunionid"] = $oricidinfo ["unionid"];
	
	if (empty ( $troops )) {
		//空白据点
		$result ["uid"] = "-1";
		$result ["unionid"] = "-1";
		sql_query ( "update sys_battle_city set uid=-1,unionid=-1,hasuser=0 where cid=$cid" );
		return $result;
	}
	
	$isFirst = true;
	foreach ( $troops as $troop ) {
		if ($isFirst) {
			$uid = $troop ["uid"];
			$unionid = $troop ["battleunionid"];
			$isFirst = false;
		} else {
			if ($troop ["battleunionid"] != $unionid) {
				//不属于同一阵营的驻扎
				sql_query ( "update sys_battle_city set uid=0,unionid=0 where cid=$cid" );
				return 0;
			}
			if ($troop ["uid"] != $uid) {
				if ($troop ["uid"] > 0 && $uid > 0) {
					//属于同一阵营但是uid不同,并且都不是NPC军队
					$sameUid = false;
				}
				$uid = $troop ["uid"];
				$unionid = $troop ["battleunionid"];
			
			}
		
		}
		if ($troop ["uid"] > 0) {
			//是否有NPC部队
			$hasuser = 1;
		}
	
	}
	
	//所有的都是同一阵营
	if ($sameUid) {
		//属于同一个用户
		sql_query ( "update sys_battle_city set uid=$uid,ownerunionid=$unionid,unionid=$unionid,hasuser='$hasuser' where cid=$cid" );
		$result ["uid"] = $uid;
		$result ["unionid"] = $unionid;
	} else {
		//同一阵营的不同用户
		sql_query ( "update sys_battle_city set uid=0,unionid=$unionid,hasuser='$hasuser' where cid=$cid" );
		$result ["uid"] = 0;
		$result ["unionid"] = $unionid;
	}
	return $result;

}
/**
 * 如果队长退出，自动转移队长，
 * @param uid, 队长的uid
 * @return unknown_type
 */
function autoTransferCaptain($uid) {
	
	$battlefield = sql_fetch_one ( "select * from sys_user_battle_field where createuid='$uid' and id=(select battlefieldid from sys_user_battle_state where state=0 and uid='$uid') " );
	if (! empty ( $battlefield )) {
		$battlefieldid = $battlefield ['id'];
		$userinfo = sql_fetch_one ( "select * from sys_user_battle_state where state=0 and battlefieldid='$battlefieldid' and uid!=$uid order by jointime limit 1" );
		if (! empty ( $userinfo )) {
			$touid = $userinfo ['uid'];
			sql_query ( "update sys_user_battle_field set createuid='$touid' where id=$battlefieldid " );
			//发战报
			sendReport ( $touid, 0, 40, 0, 0, $GLOBALS ['battle'] ['become_captain'] );
		}
	}

}

function punishNotAdult($uid) {
	if ($uid == "")
		return 1;
	$row = sql_fetch_one ( "select * from sys_user_fcm where uid=$uid" );
	if (empty ( $row ))
		return 1;
	$login_time = $row ["login_time"];
	$logout_time = $row ["logout_time"];
	$online_time = $row ["onlinetime"];
	$offline_time = $row ["offline_time"];
	
		if($row["state"] == 1 || $row["state"] == 5) //成人 或者 审核中
{
		return 1;
	}
	$online_time = intval ( intval ( $online_time ) / 60 );
	
	if ($online_time > 3 * 30 && $online_time < 5 * 60) {
		return 0.5;
	} else if ($online_time >= 5 * 60) {
		return 0;
	}
	return 1;
}

function enoughIntervalFromLastMark($battlefieldid, $markid, $option1, $update = false, $interval = 300) {
	$lastRunInfo = sql_fetch_one ( "SELECT * FROM mem_battle_event_mark WHERE battlefieldid=$battlefieldid AND mark_id=$markid AND option1=$option1" );
	$lastRunTime = 0;
	if (! empty ( $lastRunInfo )) {
		$lastRunTime = $lastRunInfo ["time"];
	}
	$nowtime = sql_fetch_one_cell ( "SELECT UNIX_TIMESTAMP()" );
	if ($nowtime - $lastRunTime >= $interval) {
		if ($update) {
			if (empty ( $lastRunInfo )) {
				sql_query ( "INSERT INTO mem_battle_event_mark(battlefieldid,mark_id,option1,`time`) VALUES($battlefieldid,$markid,$option1,UNIX_TIMESTAMP())" );
			} else {
				sql_query ( "UPDATE mem_battle_event_mark SET `time`=UNIX_TIMESTAMP() WHERE id=$lastRunInfo[id]" );
			}
		}
		return true;
	} else {
		return false;
	}
}
function addTroopThings($uid, $troopid, $tid, $cnt) {
	sql_query ( "insert into sys_troop_things (uid,troop_id,thing_id,`count`) values ('$uid','$troopid','$tid','$cnt') on duplicate key update `count`=`count`+$cnt" );
}
function removeTroopThings($troopid, $tid) {
	sql_query ( "delete from sys_troop_things where troop_id=$troopid and thing_id=$tid" );
}
function getChatMsg($uid, $bid, $hid, $userHid, $myTroopId, $targetTroopId) {
	$ret = array ();
	$events = sql_fetch_rows ( "select * from cfg_battle_event where bid=$bid and  triggertype=8 AND targettype=0 and triggerid=$hid order by id" );
	//查找第一个符合条件的
	foreach ( $events as $event ) {
		$pass = false;
		if ($event ["targettype2"] == 0) {
			$pass = true;
		} else if ($event ["targettype2"] == 6) {
			//判定任务完成
			$gid = sql_fetch_one_cell ( "select id from cfg_task_goal where tid='$event[targetid2]'" );
			if (sql_check ( "select 1 from sys_user_goal where uid=$uid and gid=$gid" )) {
				$pass = true;
			}
		}
		if ($pass) {
			$ahid = $hid;
			if ($event ["targetid1_option"] > 0) {
				$ahid = $event ["targetid1_option"];
			}
			if ($event ["targetid1_option"] >= 0) {
				$ret [] = sql_fetch_one ( "select sex,face from cfg_battle_hero where hid='$ahid'" );
			} else {
				$ret [] = sql_fetch_one ( "select sex,face from sys_city_hero where hid='$userHid'" );
			}
			$ret [] = $event [msg];
		}
	}
	if (count ( $ret ) == 0) {
		$ret [] = sql_fetch_one ( "select sex,face from cfg_battle_hero where hid='$hid'" );
		$ret [] = $GLOBALS ['battle'] ['defaut_chat_msg'];
	}
	return $ret;
}
function chatWithHero($uid, $param) {
	$troopid = intval ( array_shift ( $param ) );
	$myTroopinfo = sql_fetch_one ( "select id,cid,battleunionid,hid from sys_troops where id='$troopid' and (state=3 or state=4)" );
	$cid = $myTroopinfo ["cid"];
	$targettroopid = intval ( array_shift ( $param ) );
	$troopinfo = sql_fetch_one ( "select cid,id,uid,hid,state,bid,battlefieldid,battleunionid from sys_troops where id='$targettroopid'" );
	if (empty ( $troopinfo ))
		throw new Exception ( $GLOBALS ['battle'] ['troop_not_exist'] );
	if (empty ( $cid ) || $cid != $troopinfo ["cid"]) {
		throw new Exception ( $GLOBALS ['battle'] ['only_talk_in_same_city'] );
	}
	$bid = $troopinfo ["bid"];
	$hid = $troopinfo ["hid"];
	$battleinfo = firstGetUserBattleInfo ( $uid );
	
	if ($troopinfo ["bid"] == 7001) {
		//三让徐州副本不能一直不断交谈
		if (! enoughIntervalFromLastMark ( $troopinfo ["battlefieldid"], 2, $hid, true )) {
			throw new Exception ( $GLOBALS ['battle'] ['too_frequent_talk'] );
		}
	}
	
	$battlefieldid = $troopinfo ["battlefieldid"];
	$unionid = $myTroopinfo ["battleunionid"];
	if ($hid == 8056) {
		//凤仪亭，跟监管说话，先转移木头
		$userCnt = sql_fetch_one_cell ( "select count from sys_troop_things where troop_id=$myTroopinfo[id]" );
		$totalCnt = intval ( sql_fetch_one_cell ( "select count from sys_troop_things where troop_id=$targettroopid" ) );
		if ($userCnt > 0) {
			addTroopThings ( 0, $targettroopid, 83, $userCnt );
			removeTroopThings ( $myTroopinfo[id], 83 );
		}
		$reqCnt = $battleinfo ["level"] * 5000;
		$totalCnt = $totalCnt + $userCnt;
		if ($totalCnt >= $reqCnt) {
			$totalCnt = $reqCnt;
			sendBattleEvent ( $troopid, $uid, $bid, $battlefieldid, $unionid, $troopinfo ["hid"], 10000 );
		}
		//取得第2阶段开始的时间
		$timeleft = sql_fetch_one_cell ( "SELECT GREATEST(15-FLOOR((UNIX_TIMESTAMP()-TIME)/60),0) FROM mem_battle_event_mark WHERE battlefieldid=$battlefieldid AND mark_id=101" );
		$msg = sprintf ( $GLOBALS ['battle'] ['8001_wood_state'], $totalCnt, $reqCnt - $totalCnt, $timeleft );
		$ret [] = sql_fetch_one ( "select sex,face from cfg_battle_hero where hid='$hid'" );
		$ret [] = $msg;
	} else if($hid == 5050) {
		$ret = array ();
		$events = sql_fetch_rows ( "select * from cfg_battle_event where bid=$bid and  triggertype=8 and triggerid=$hid order by id" );
		//查找第一个符合条件的
		foreach ( $events as $event ) {
			$pass = false;
			if ($event ["targettype2"] == 0) {
				$pass = true;
			} else if ($event ["targettype2"] == 6) {
				//判定任务完成
				$gid = sql_fetch_one_cell ( "select id from cfg_task_goal where tid='$event[targetid2]'" );
				if (sql_check ( "select 1 from sys_user_goal where uid=$uid and gid=$gid" )) {
					$pass = true;
				}
			}
			if ($pass) {
				$ahid = $hid;
				if ($event ["targetid1_option"] > 0) {
					$ahid = $event ["targetid1_option"];
				}
				if ($event ["targetid1_option"] >= 0) {
					$ret [] = sql_fetch_one ( "select sex,face from cfg_battle_hero where hid='$ahid'" );
				} else {
					$ret [] = sql_fetch_one ( "select sex,face from sys_city_hero where hid='$userHid'" );
				}
				$ret [] = $event [msg];
			}
		}
		if (count ( $ret ) == 0) {
			$ret [] = sql_fetch_one ( "select sex,face from cfg_battle_hero where hid='$hid'" );
			$ret [] = $GLOBALS ['battle'] ['defaut_chat_msg'];
		}
		
	}
	else {
		$ret = getChatMsg ( $uid, $bid, $hid, $myTroopinfo ["hid"], $myTroopinfo ["id"], $targettroopid );
	}
	
	sendBattleEvent ( $troopid, $uid, $bid, $battlefieldid, $unionid, $troopinfo ["hid"], 8 );
	return $ret;
}

function sendBattleMsg($battlefieldid, $unionid, $msg) {
	sql_insert ( "insert into `mem_war_event` (`battleid`, `unionid`,`type`,`content`,`evttime`) values ('$battlefieldid', '$unionid', 1, '$msg',unix_timestamp())" );
	sql_query ( "INSERT INTO `log_battle_news` (`battleid`, `unionid`, `content`, `log_time`)  VALUES ('$battlefieldid', '$unionid', '$msg', unix_timestamp())" );
}
function sendBattleEvent($troopid, $uid, $bid, $battlefieldid, $unionid, $triggerid, $eventtype) {
	sql_query ( "insert into mem_battle_event(troop_id,uid,bid,battlefieldid,unionid,trigger_id,event_type) values($troopid,$uid,$bid,$battlefieldid,$unionid,$triggerid,$eventtype)" );
}

function getBattleDescription($bid) {
	return sql_fetch_one_cell ( "select content from cfg_battle_field where id=$bid" );
}

function getBattleName($bid) {
	//$name = $GLOBALS['battleState'][$bid]["name"];
	$battle = sql_fetch_one_cell ( "select count(*) from cfg_battle_field where id=$bid" );
	if ($battle == 0) {
		return "unknown";
	
	} else {
		return sql_fetch_one_cell ( "select name from cfg_battle_field where id=$bid" );
	}
	
//return $name;
}

function getBattleCondition($bid) {
	return $GLOBALS ['battleState'] [$bid] ["condition"];
}

define ( "STATE_CLOSE", 0 );
define ( "STATE_UNKNOWN", 1 );
define ( "STATE_OPEN", 2 );

	function getBattleState($bid)
	{
		$batteState = array (
			1001 => STATE_OPEN, 
			1002 => STATE_UNKNOWN, 
			1003 => STATE_UNKNOWN, 
			2001 => STATE_OPEN, 
			5001 => STATE_UNKNOWN, 
			5002 => STATE_UNKNOWN, 
			3001 => STATE_OPEN, 
			3002 => STATE_UNKNOWN, 
			4001 => STATE_UNKNOWN, 
			4002 => STATE_CLOSE
		);
	return sql_fetch_one_cell ( "select state from cfg_battle_field where id=$bid" );
	//return $batteState[$bid];
}
function getTaskState($t) {
	define ( "TASK_CLOSE", 0 );
	define ( "TASK_OPEN", 1 );
	define ( "TASK_FINISH", 2 );
	$taskState = array (1 => TASK_OPEN, 2 => TASK_OPEN );
	return $taskState [$t];
}
function getBattleActiveState($uid, $param) {
	
	$battleOpen = sql_fetch_rows ( "select id,name,content from cfg_battle_field where state != 0" );
	$battleClose = sql_fetch_rows ( "select id,name,openCondition from cfg_battle_field where state = 0" );
	$unknowStateBattle = sql_fetch_rows ( "select distinct(a.battleId) as id from sys_battle_open_condition a ,cfg_battle_field b where b.state = 0 and a.dependBattleId = b.id  " );
	$ret [] = $battleOpen;
	$ret [] = $battleClose;
	$ret [] = $unknowStateBattle;
	return $ret;
}
//获得战场双倍时间
function getBattleTime($uid, $param) {
	$ret = array ();
	$rows = sql_fetch_rows ( "select * from cfg_act_battle where state>0 and (date=0 || (date>0 and unix_timestamp()<=date+86400)) order by date" );
	foreach ( $rows as &$row ) {
		$weektoday = intval ( sql_fetch_one_cell ( "select weekday(from_unixtime(unix_timestamp()))+1" ) );
		if ($row ["date"] == 0 && $row ["actid"] <= 7) { //每周定期
			$date = sql_fetch_one_cell ( "select DATE_FORMAT(from_unixtime(unix_timestamp()+($row[actid]-$weektoday)*86400),'%m.%d')" );
			$weekday = $row ["actid"];
		} else {
			$date = sql_fetch_one_cell ( "select DATE_FORMAT(from_unixtime($row[date]),'%m.%d')" );
			$weekday = sql_fetch_one_cell ( "select weekday(from_unixtime($row[date]))+1" );
		}
		$row ["date"] = $date;
		$row ["weekday"] = $weekday;
	}
	$ret [] = $rows;
	return $ret;
}

function leaveQueue($uid,$param) {
	$bid = intval($param);
	sql_query("delete from sys_battlenet_waiting_queue where uid=$uid and bid='$bid'");
}
function joinQueue($uid,$param) {
	$bid = intval($param);	
	$sql = "insert into sys_battlenet_waiting_queue(bid,uid) values($bid,$uid) on duplicate key update bid=$bid";
	sql_query($sql);
}

function getBattleNetUrl($uid,$param) {
//	$bid = array_shift($param);
	$ret = array ();
	if (defined('BATTLE_NET_ENABLE')&&BATTLE_NET_ENABLE) {
		$ret[]=sql_fetch_rows("select bid, url, chathost,chatport from cfg_battlenet_server");
	}else{
		$ret[]= array();
	}
	return $ret;
}


?>