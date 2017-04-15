<?php

function checkOnline($uid,$param)
{
	$online_time = -1;
	//$key = "A7D5A4F62D7EE2A043BECCE2C36231C3";
	$key='#0T65w[!uQpmYx!D>)[VvpotN)EuwLgg+oiw{%)s+L)';
	//$url = "http://interface1.uuyx.com/GameOnlineInfo.aspx?"; 
	$gameid=1;
	$url="http://www.rxsgfcm.com/onlineinfo?gameid=$gameid&";
	$cardid = urlencode(array_shift($param));
	$time  = urlencode(array_shift($param));
	$interval = intval(array_shift($param));
	$returnState = 3;//未审核
	 if($cardid){
	 	$alarmErrMsg = "";	 
		 $sign = md5($gameid.$cardid."$time"."$key");
		 $uparams = "id_card=$cardid&time_stamp=$time&sign=$sign";
		 $url .= $uparams;
		 try {
		 	$info = file_get_contents($url);
		 	//file_put_contents("/bloodwar/server/game/passport/log.log",$url."\n".$info."\n",FILE_APPEND);
		 } catch (Exception $e) {
		 	//error_log("防沉迷异常：未返回 $url 异常:".$e->getMessage());
		 	error_log(sprintf($GLOBALS['adult']['error'],$url).$e->getMessage());
		 	$ret = array();
		 	$ret[] = 0;
		 	$ret[] = 1;
		 	return $ret;
		 } 
		 if($info != NULL){
		     $ret_ary = explode("&", $info);
		     $verify_state = explode("=", $ret_ary[1]);
			 $online_times = explode("=", $ret_ary[2]);
		     
			 if (!($verify_state[1]==1||$verify_state[1]==2||$verify_state[1]==3||$verify_state[1]==4||$verify_state[1]==5)) 
			 {		 	
			 	//error_log("防沉迷异常：$url 返回: $info");
			 	error_log(sprintf($GLOBALS['adult']['error2'],$url,$info).$e->getMessage());
			 	$ret = array();
			 	$ret[] = 0;
			 	$ret[] = 1;
			 	return $ret;			 	
			 }			 
			 $returnState= $verify_state[1];
			 $online_time = $online_times[1]; //从返回值取
			 sql_query("insert into sys_user_online(uid, state) values($uid, $verify_state[1]) on duplicate key update state=$verify_state[1]");
			 			 
			 if($online_time == NULL)
			 {
			 	$online_time = -1;
			 }
		 else{
			 	$online_time = 60 * intval($online_time);
				sql_query("update sys_user_online set online_time=$online_time where uid=$uid ");
				//在这里处理下，没有通过防沉迷的所有cardid相同的在线时间累加。
				$online_time = sql_fetch_one_cell("select sum(greatest(a.lastupdate-a.onlineupdate,0)) from sys_online a,sys_user_online b where a.uid=b.uid and b.cardid='$cardid' and b.state not in (1,5)");
			 }
		 }
		 //insert into online time into 
		//sql_query("insert into sys_user_online(uid, online_time) values($uid, $online_time) on duplicate key update online_time=$online_time ");
	 }
	 else{
	 	//sql_query("insert into sys_user_online(uid, online_time) values($uid, $online_time) on duplicate key update online_time +=$interval ");
	 	$interval = $interval*5;   //客户端改成5分钟检测一次，此处就乘上5
	 	sql_query("update sys_user_online set online_time=online_time+$interval where uid=$uid ");
	 	$online_time = sql_fetch_one_cell("select online_time from sys_user_online where uid='$uid'");
	 }
	 
	//return online time
	$ret = array();
	$ret[] = $online_time;
	if($returnState==5){//审核中 不需要防沉迷
		$returnState = 1;
	}
	$ret[] = $returnState;
	return $ret;
}

function updateOnlineTime($uid)
{
	$user = sql_fetch_one("select * from sys_user_online where uid='$uid'");
	$now = sql_fetch_one_cell("select unix_timestamp()");
	
	$useronlineinfo = sql_fetch_one("select * from sys_online where uid='$uid'");
	if(!empty($useronlineinfo)) {
		$lastupdate = $useronlineinfo['lastupdate'];
		$onlineupdate = $useronlineinfo['onlineupdate'];
		
		$onlinetimefll = $lastupdate - $onlineupdate;
		if($onlinetimefll < 0) $onlinetimefll = 0;
	} else {
		$onlinetimefll = 0;
	}
	
	if(!empty($user))
	{
		//$offline_time = $now - $user['login_time'] + $user['offline_time'] - $onlinetimefll;
		$offline_time = $now - $user['login_time'] - $onlinetimefll;
		if($offline_time > 5*60*60)
			sql_query("update sys_user_online set login_time=$now, offline_time=0, online_time=0 where uid=$uid");
		else
			sql_query("update sys_user_online set login_time=$now, offline_time=$offline_time where uid=$uid");
	}
	else{
		sql_query("insert into sys_user_online(uid, login_time) values($uid, $now) on duplicate key update login_time=$now");
	}
	$ret = array();
	$online_time = 0;
	if($offline_time > 5*60*60)
		$online_time = 0;
	else
		$online_time =  $user['online_time'];
	$ret[] = $online_time;
	return $ret;
}

function updateLoginTime($uid, $param)
{
	$user = sql_fetch_one("select * from sys_user_online where uid='$uid'");
	if(!empty($user)) {
		$ret[] = $user['online_time'];
	} else {
		$ret[] = 0;
	}
	return $ret;
}

//一下是新的
function updateFcmTime($uid) {
	global $GLOBAL_ADULT_RET;
	sql_query("insert into sys_user_fcm(uid, state,cardid) values($uid, $GLOBAL_ADULT_RET[1],'$GLOBAL_ADULT_RET[0]') on duplicate key update state=$GLOBAL_ADULT_RET[1],cardid='$GLOBAL_ADULT_RET[0]'");
	if ($GLOBAL_ADULT_RET[1]==1 || $GLOBAL_ADULT_RET[1]==5) {
		sql_query("update sys_user_fcm set onlinetime=0,state=1,lastupdate=unix_timestamp() where uid=$uid and cardid='$GLOBAL_ADULT_RET[0]'");
	} else if (!empty($GLOBAL_ADULT_RET[0])){
		$count1 = sql_fetch_one_cell("select count(*) from sys_user_fcm where cardid='$GLOBAL_ADULT_RET[0]'");
		$count2 = sql_fetch_one_cell("select count(*) from sys_user_fcm where cardid='$GLOBAL_ADULT_RET[0]' and unix_timestamp()-lastupdate>=5*3600");
		//都大于5个小时了，才解开
		if (($count1 > 0) && ($count1 == $count2)) {
			sql_query("update sys_user_fcm set onlinetime=0 where cardid='$GLOBAL_ADULT_RET[0]'");
		}
	} else if (empty($GLOBAL_ADULT_RET[0])) {
		sql_query("update sys_user_fcm set onlinetime=0 where uid=$uid and unix_timestamp()-lastupdate>=5*3600");
	}
	sql_query("update sys_user_fcm set lastupdate=unix_timestamp() where uid=$uid");
	
}

function checkFcmOnline($uid,$param) {
	$cardid = urlencode(array_shift($param));
	$onlineTime=0;
	$state= sql_fetch_one_cell("select value from mem_state where state=11");
	sql_query("update sys_user_fcm set onlinetime=onlinetime+unix_timestamp()-lastupdate where uid=$uid");//和下面的顺序不能反
	sql_query("update sys_user_fcm set lastupdate=unix_timestamp() where uid=$uid");
	if (empty($cardid) || empty($state)) {
		$onlineTime = sql_fetch_one_cell("select onlinetime from sys_user_fcm where uid=$uid");
	} else {
		$onlineTime = sql_fetch_one_cell("select sum(onlinetime) from sys_user_fcm where cardid='$cardid'");
	}
	$ret = array();
	$ret[]=$onlineTime;
	return  $ret;
}















